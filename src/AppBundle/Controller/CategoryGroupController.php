<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\CategoryGroup;
use AppBundle\Form\CategoryGroupType;
use AppBundle\Repository\CategoryRepository;
use AppBundle\Repository\CategoryGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;

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
     * @Template("CategoryGroup/index.html.twig")
     */
    public function indexAction()
    {
        return ['category_groups' => $this->categoryGroupRepository->findAll()];
    }

    /**
     * @Route("/new", name="category-group_new")
     * @Template("CategoryGroup/new.html.twig")
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

            return $this->redirectToRoute('category-group_show', ['id' => $categoryGroup->getId()]);
        }

        return [
            'category_group' => $categoryGroup,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{id}", name="category-group_show")
     * @Template("CategoryGroup/show.html.twig")
     */
    public function showAction(CategoryGroup $categoryGroup)
    {
        return [
            'categories' => $this->categoryRepository->getByGroup($categoryGroup),
            'category_group' => $categoryGroup,
        ];
    }

    /**
     * @Route("/{id}/edit", name="category-group_edit")
     * @Template("CategoryGroup/edit.html.twig")
     */
    public function editAction(Request $request, CategoryGroup $categoryGroup)
    {
        $editForm = $this->createForm(CategoryGroupType::class, $categoryGroup);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('category-group_show', ['id' => $categoryGroup->getId()]);
        }

        return [
            'category_group' => $categoryGroup,
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * @Route("/{id}/delete", name="category-group_delete")
     */
    public function deleteAction(CategoryGroup $categoryGroup)
    {
        $categoriesCount = $this->categoryRepository->getCountByGroup($categoryGroup);

        if ($categoriesCount > 1) {
            $this->addFlash('danger', 'This group still contains some categories.');

            return $this->redirectToRoute('category-group_show', ['id' => $categoryGroup->getId()]);
        }

        else {
            $this->em->remove($categoryGroup);
            $this->em->flush();
        }

        return $this->redirectToRoute('category-group_index');
    }
}
