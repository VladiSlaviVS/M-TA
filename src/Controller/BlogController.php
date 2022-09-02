<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ArticleCategory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route(path: '/blog/{categoryId}', name: 'app_blog')]
    public function index(EntityManagerInterface $em, int $categoryId = 0): Response
    {

        $categoryRepo = $em->getRepository(ArticleCategory::class);
        $articleRepository = $em->getRepository(Article::class);
        $categories = $categoryRepo->findAll();
        $category = null;
        if ($categoryId) {
            foreach ($categories as $cat) {
                if ($cat->getId() === $categoryId) {
                    $category = $cat;
                    break;
                }
            }
        }

        $articles = $articleRepository->getActiveCategoryArticles($category);
        return $this->render('blog/list.html.twig', [
            'categories' => $categories,
            'articles' => $articles,
            'currentCategory' => $category ? $category->getId(): 0
        ]);
    }

    #[Route(path: '/blog/article/{id}', name: 'app_blog_article')]
    public function details(EntityManagerInterface $em, int $id): Response {
        $articleRepository = $em->getRepository(Article::class);
        $article = $articleRepository->find($id);
        if (!$article || !$article->isIsActive()) {
            return $this->render('404.html.twig');
        }

        return $this->render('blog/details.html.twig', [
            'article' => $article
        ]);
    }

    #[Route(path: '/blog/markRead/{id}', name: 'app_blog_mark_read')]
    public function markRead(EntityManagerInterface $em, int $id): Response {
        $articleRepository = $em->getRepository(Article::class);
        $article = $articleRepository->find($id);
        if (!$article || !$article->isIsActive()) {
            return $this->render('404.html.twig');
        }

        $article->addUser($this->getUser());
        $em->flush();

        return $this->render('blog/marked-as-read.html.twig', [
            'article' => $article
        ]);
    }
}