<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Form\CategoryType;
use AppBundle\Repository\CategoryRepository;
use AppBundle\Repository\TransactionRepository;
use AppBundle\Repository\CategoryGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @IsGranted("ROLE_ADMIN")
 * @Route("category")
 */
class CategoryController extends Controller
{
    private $em;
    private $categoryRepository;
    private $transactionRepository;
    private $categoryGroupRepository;

    public function __construct(
        EntityManagerInterface $em,
        CategoryRepository $categoryRepository,
        TransactionRepository $transactionRepository,
        CategoryGroupRepository $categoryGroupRepository)
    {
        $this->em = $em;
        $this->categoryRepository = $categoryRepository;
        $this->transactionRepository = $transactionRepository;
        $this->categoryGroupRepository = $categoryGroupRepository;
    }

    /**
     * @Route("/", name="category_index")
     */
    public function indexAction()
    {
        $categories = $this->categoryRepository->findAll();

        return $this->render('Category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/new", name="category_new")
     */
    public function newAction(Request $request)
    {
        $category = new Category();

        if ($request->query->get('group')) {
            $group = $this->categoryGroupRepository->find($request->query->get('group'));
            $category->setGroup($group);
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($category);
            $this->em->flush();

            return $this->redirectToRoute('category_show', [
                'id' => $category->getId()
            ]);
        }

        return $this->render('Category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="category_show")
     */
    public function showAction(Category $category)
    {
        $deleteForm = $this->createDeleteForm($category);

        $transactionsCount = $this->transactionRepository->getCountByCategory($category);
        $displayDeleteForm = $transactionsCount == 0;

        return $this->render('Category/show.html.twig', [
            'category' => $category,
            'delete_form' => $deleteForm->createView(),
            'display_delete_form' => $displayDeleteForm,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="category_edit")
     */
    public function editAction(Request $request, Category $category)
    {
        $editForm = $this->createForm(CategoryType::class, $category);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('category_show', [
                'id' => $category->getId()
            ]);
        }

        return $this->render('Category/edit.html.twig', [
            'category' => $category,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="category_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Category $category)
    {
        $form = $this->createDeleteForm($category);
        $form->handleRequest($request);

        if ($category === $category->getGroup()->getDefaultCategory()) {
            throw new BadRequestHttpException();
        }

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $this->em->remove($category);
                $this->em->flush();
            } catch (ForeignKeyConstraintViolationException $e) {
                $this->addFlash('danger', 'This Category is still used in some transactions.');

                return $this->redirectToRoute('category_show', ['id' => $category->getId()]);
            }
        }

        return $this->redirectToRoute('category_index');
    }

    /**
     * @param Category $category
     * @return \Symfony\Component\Form\Form
     */
    private function createDeleteForm(Category $category)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('category_delete', ['id' => $category->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
