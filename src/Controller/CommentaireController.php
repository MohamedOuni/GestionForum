<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Post;
use App\Form\CommentType;
use App\Repository\CommentaireRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
class CommentaireController extends AbstractController
{
    /**
     * @Route("/commentaire", name="app_comments")
     */
    public function index(CommentaireRepository $commentaireRepository): Response
    {
        return $this->render('commentaire/index.html.twig', [
            'comments' => $commentaireRepository->findAll(),
        ]);
    }
    /**
     * @Route("/ajout/commentaire/{id}", name="ajoutspost")
     */
    public function addcomment(Post $post,Request $request,UserRepository $repository): Response
    {
        $comment = new Commentaire();
        $form = $this->createForm(CommentType::class,$comment);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $comment->setPost($post);
            $user = $repository->find(2);
            $comment->setUser($user);
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('app_comments');

        }
        return $this->render('commentaire/ajout.html.twig',['post'=>$post,'form'=>$form->createView()]);
    }

    /**
     * @Route("/commentaire/remove/{id}", name="deletecomment")
     */
    public function deleteCommande(Commentaire $commentaire): Response
    {
        $em= $this->getDoctrine()->getManager();
        $em->remove($commentaire);
        $em->flush();
        return $this->redirectToRoute('app_comments');
    }


    /**
     * @Route("/update/commentaire/{id}", name="updatecomment")
     */
    public function update(Request $request,UserRepository $repository ,Commentaire $comment): Response
    {
        $form = $this->createForm(CommentType::class,$comment);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $user = $repository->find(2);
            $comment->setUser($user);
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('app_comments');

        }
        return $this->render('commentaire/ajout.html.twig',['commentaire'=>$comment,'form'=>$form->createView()]);
    }



}
