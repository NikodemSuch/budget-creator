<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Form\CategoryType;
use AppBundle\Repository\CategoryRepository;
use AppBundle\Repository\TransactionRepository;
use AppBundle\Repository\CategoryGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;

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
     * @Template("Category/index.html.twig")
     */
    public function indexAction()
    {
        return ['categories' => $this->categoryRepository->findAll()];
    }

    /**
     * @Route("/new", name="category_new")
     * @Template("Category/new.html.twig")
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

            return $this->redirectToRoute('category_show', ['id' => $category->getId()]);
        }

        return [
            'category' => $category,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{id}", name="category_show")
     * @Template("Category/show.html.twig")
     */
    public function showAction(Category $category)
    {
        $transactionsCount = $this->transactionRepository->getCountByCategory($category);
        $displayDeleteButton = $transactionsCount == 0;

        return [
            'category' => $category,
            'display_delete_button' => $displayDeleteButton,
        ];
    }

    /**
     * @Route("/{id}/edit", name="category_edit")
     * @Template("Category/edit.html.twig")
     */
    public function editAction(Request $request, Category $category)
    {
        $editForm = $this->createForm(CategoryType::class, $category);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('category_show', ['id' => $category->getId()]);
        }

        return [
            'category' => $category,
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * @Route("/{id}/delete", name="category_delete")
     */
    public function deleteAction(Category $category)
    {
        if ($category === $category->getGroup()->getDefaultCategory()) {
            throw new BadRequestHttpException();
        }

        try {
            $this->em->remove($category);
            $this->em->flush();
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->addFlash('danger', 'This Category is still used in some transactions.');

            return $this->redirectToRoute('category_show', ['id' => $category->getId()]);
        }

        return $this->redirectToRoute('category_index');
    }
}
