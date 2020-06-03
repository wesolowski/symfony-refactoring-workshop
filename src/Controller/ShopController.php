<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class ShopController extends AbstractController
{
    /**
     * @var \App\Repository\ArticleRepository
     */
    private $articleRepository;

    /**
     * @var \App\Repository\CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var \App\Repository\Object2categoryRepository
     */
    private $object2categoryRepository;

    /**
     * @param \App\Repository\ArticleRepository $articleRepository
     * @param \App\Repository\CategoryRepository $categoryRepository
     * @param \App\Repository\Object2categoryRepository $object2categoryRepository
     */
    public function __construct(\App\Repository\ArticleRepository $articleRepository, \App\Repository\CategoryRepository $categoryRepository, \App\Repository\Object2categoryRepository $object2categoryRepository)
    {
        $this->articleRepository = $articleRepository;
        $this->categoryRepository = $categoryRepository;
        $this->object2categoryRepository = $object2categoryRepository;
    }


    /**
     * @Route("/", name="home", methods={"GET"})
     */
    public function home(): Response
    {
        $catsIdsDbResult = $this->object2categoryRepository
            ->createQueryBuilder('o')
            ->select('o.catnid')
            ->distinct()
            ->getQuery()
            ->getResult();

        $catsIds = array_column($catsIdsDbResult, 'catnid');

        $categories = $this->categoryRepository->findBy(['id' => $catsIds]);

        return $this->render('shop/home.html.twig', [
            'categories' => $categories,
        ]);
    }


    /**
     * @Route("/category/{id}", name="category", methods={"GET"})
     */
    public function category(Category $category): Response
    {
        $articlesIdsDbResult = $this->object2categoryRepository
            ->createQueryBuilder('o')
            ->select('o.objectid')
            ->where('o.catnid = :catnid')
            ->setParameter('catnid', $category->getId())
            ->getQuery()
            ->getResult();

        $articlesIds = array_column($articlesIdsDbResult, 'objectid');

        $articles = $this->articleRepository->findBy(['id' => $articlesIds]);

        return $this->render('shop/category.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/article/{id}", name="article", methods={"GET"})
     */
    public function article(Article $article): Response
    {
        return $this->render('shop/article.html.twig', [
            'article' => $article,
        ]);
    }
}
