<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\CategoryGroup;
use AppBundle\Form\CategoryGroupType;
use AppBundle\Repository\CategoryRepository;
use AppBundle\Repository\CategoryGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @IsGranted("ROLE_ADMIN")
 * @Route("category-group")
 */
class CategoryGroupController extends Controller
{
    private $em;
    private $categoryRepository;
    private $categoryGroupRepository;

    public function __construct(
        EntityManagerInterface $em,
        CategoryRepository $categoryRepository,
        CategoryGroupRepository $categoryGroupRepository)
    {
        $this->em = $em;
        $this->categoryRepository = $categoryRepository;
        $this->categoryGroupRepository = $categoryGroupRepository;
    }

    /**
     * @Route("/", name="category-group_index")
     */
    public function indexAction()
    {
        $categoryGroups = $this->categoryGroupRepository->findAll();

        return $this->render('CategoryGroup/index.html.twig', [
            'category_groups' => $categoryGroups,
        ]);
    }

    /**
     * @Route("/new", name="category-group_new")
     */
    public function newAction(Request $request)
    {
        $categoryGroup = new CategoryGroup();
        $form = $this->createForm(CategoryGroupType::class, $categoryGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $defaultCategory = new Category();
            $defaultCategory->setName($categoryGroup->getName()." - Other");
            $defaultCategory->setGroup($categoryGroup);
            $categoryGroup->setDefaultCategory($defaultCategory);

            $this->em->persist($categoryGroup);
            $this->em->flush();

            return $this->redirectToRoute('category-group_show', [
                'id' => $categoryGroup->getId()
            ]);
        }

        return $this->render('CategoryGroup/new.html.twig', [
            'category_group' => $categoryGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="category-group_show")
     */
    public function showAction(CategoryGroup $categoryGroup)
    {
        $deleteForm = $this->createDeleteForm($categoryGroup);
        $categories = $this->categoryRepository->getByGroup($categoryGroup);

        return $this->render('CategoryCroup/show.html.twig', [
            'categories' => $categories,
            'category_group' => $categoryGroup,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="category-group_edit")
     */
    public function editAction(Request $request, CategoryGroup $categoryGroup)
    {
        $editForm = $this->createForm(CategoryGroupType::class, $categoryGroup);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('category-group_show', [
                'id' => $categoryGroup->getId()
            ]);
        }

        return $this->render('CategoryGroup/edit.html.twig', [
            'category_group' => $categoryGroup,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="category-group_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, CategoryGroup $categoryGroup)
    {
        $form = $this->createDeleteForm($categoryGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categoriesCount = $this->categoryRepository->getCountByGroup($categoryGroup);

            if ($categoriesCount > 1) {
                $this->addFlash('danger', 'This group still contains some categories.');

                return $this->redirectToRoute('category-group_show', ['id' => $categoryGroup->getId()]);
            }

            else {
                $this->em->remove($categoryGroup);
                $this->em->flush();
            }
        }

        return $this->redirectToRoute('category-group_index');
    }

    /**
     * @param CategoryGroup $categoryGroup
     * @return \Symfony\Component\Form\Form
     */
    private function createDeleteForm(CategoryGroup $categoryGroup)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('category-group_delete', ['id' => $categoryGroup->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
