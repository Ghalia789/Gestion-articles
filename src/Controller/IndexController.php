<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\PropertySearch;
use App\Entity\CategorySearch;
use App\Entity\PriceSearch;
use App\Form\CategoryType;
use App\Form\ArticleType;
use App\Form\PropertySearchType;
use App\Form\CategorySearchType;
use App\Form\PriceSearchType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class IndexController extends AbstractController
{
    /*#[Route('/{name}', name: 'Ghalia')]
    */ 
    #[Route("/",name:'article_list')]
    public function home(Request $request, EntityManagerInterface $entityManager): Response
    {
        $propertySearch = new PropertySearch();
        $form = $this->createForm(PropertySearchType::class,$propertySearch);
        $form->handleRequest($request);
        $articles= [];
        if($form->isSubmitted() && $form->isValid()) {
            $nom = $propertySearch->getNom(); 
            if ($nom !== null) {
                    $articles = $entityManager->getRepository(Article::class)->createQueryBuilder('a')
                    ->where('a.Nom LIKE :nom')
                    ->setParameter('nom', '%' . $nom . '%')
                    ->getQuery()
                    ->getResult();
                } else {
                $articles = $entityManager->getRepository(Article::class)->findAll();
            }
        }
        return $this->render('articles/index.html.twig',[ 'form'=>$form->createView(),'articles' => $articles]); 
    }

    #[Route('/article/save')]
    public function createArticle(EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $article->setNom('Article 3');
        $article->setPrix(3000);
        $entityManager->persist($article);
        $entityManager->flush();
        return new Response('Article enregisté avec id '.$article->getId());
    }

    #[Route('/article/newArticle', name:'ajout_article', methods: ['GET', 'POST'])]
    public function newArticle(EntityManagerInterface $entityManager, FormFactoryInterface $formFactory, Request $request)
    {
        $article = new Article();
    
        $form = $formFactory->create(ArticleType::class, $article);

        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData(); /*getClickedButton()->getName() === 'save' ? $form->getData() : $article;*/
            $entityManager->persist($article);
            $entityManager->flush();
    
            return $this->redirectToRoute('article_list');
        }
    
        return $this->render('articles/new.html.twig', [
            'form' => $form->createView()
        ]);
    }
    

    #[Route('/article/{id}', name: 'article_show', methods: ['GET', 'POST'])]
    public function showbyId(EntityManagerInterface $entityManager, int $id): Response
    {
        $article = $entityManager->getRepository(Article::class)->find($id);
        return $this->render('articles/show.html.twig', ['article' => $article]);
    }

    #[Route('/article/edit/{id}', name: 'article_edit', methods: ['GET', 'POST'])]
    public function editArticle(Request $request,FormFactoryInterface $formFactory, EntityManagerInterface $entityManager, int $id): Response
    {
        $article = $entityManager->getRepository(Article::class)->find($id);
        
        $form = $formFactory->create(ArticleType::class, $article);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('article_list');
        }
        
        return $this->render('articles/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article
        ]);
    }
    #[Route('/article/delete/{id}', name: 'article_delete', methods:['GET','DELETE'])]
    public function deleteArticle(EntityManagerInterface $entityManager, int $id)
    {
        $article = $entityManager->getRepository(Article::class)->find($id);
        $entityManager->remove($article);
        $entityManager->flush();
        $response = new Response();
        $response->send();
        return $this->redirectToRoute('article_list');
    }

    #[Route('/category/newCat', name: 'new_category', methods:['GET','POST'])]
    public function newCategory(EntityManagerInterface $entityManager,Request $request) {
        $category = new Category();
        $form = $this->createForm(CategoryType::class,$category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
        $article = $form->getData();
        $entityManager->persist($category);
        $entityManager->flush();
        return $this->redirectToRoute('article_list');
        }
        return $this->render('articles/newCategory.html.twig',['form'=>$form->createView()]);
    }

    #[Route('/art_cat/', name: 'article_par_cat', methods:['GET','POST'])]
    public function articleParCategorie(EntityManagerInterface $entityManager,Request $request){
        $categorySearch = new CategorySearch();
        $form = $this->createForm(CategorySearchType::class, $categorySearch);
        $form->handleRequest($request);

        $articles=[];

        if($form->isSubmitted()&& $form->isValid()){
            $category = $categorySearch->getCategory();

            if($category!==null){
                $articles=$category->getArticles();
            }else{
                $articles = $entityManager->getRepository(Article::class)->findAll();
            }
        }
        return $this->render('articles/articlesParCategorie.html.twig',['form' => $form->createView(),
                                'articles' => $articles]);
    }

    #[Route('/art_prix/', name: 'article_par_prix', methods:['GET','POST'])]
    public function articlesParPrix(EntityManagerInterface $entityManager,Request $request)
    {
        $priceSearch = new PriceSearch();
        $form = $this->createForm(PriceSearchType::class,$priceSearch);
        $form->handleRequest($request);
        
        $articles= [];
        if($form->isSubmitted() && $form->isValid()) {
            $minPrice = $priceSearch->getMinPrice();
            $maxPrice = $priceSearch->getMaxPrice();
            $articles = $entityManager->getRepository(Article::class)->findByPriceRange($minPrice,$maxPrice);
        }
    return $this->render('articles/articlesParPrix.html.twig',[ 'form' =>$form
    ->createView(), 'articles' => $articles]);
    }
}

?>

