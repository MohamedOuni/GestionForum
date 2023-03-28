<?php

namespace App\Controller;
use App\Entity\User;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Validator\Constraints\Json;


class PostController extends AbstractController
{
    /**
     * @Route("/tri", name="triii")
     */
    public function Tri(Request $request,PostRepository $repository): Response
    {
        // Retrieve the entity manager of Doctrine
        $order=$request->get('type');
        if($order== "Croissant"){
            $posts = $repository->tri_asc();
        }
        else {
            $posts = $repository->tri_desc();
        }
        // Render the twig view
        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }

    /**
     * @Route("/post", name="app_post")
     */
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    /**
     * @Route("/ajouter/post", name="add_post")
     */
    public function addpost(PostRepository $postRepository,Request $request,UserRepository $repository): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class,$post);
        $form->handleRequest($request);
        $user = $repository->find(1);
        if($form->isSubmitted() && $form->isValid()){
            $post->setUser($user);
            $post->setDate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            $this->addFlash('info'
            ,'Added successfuly');

            return $this->redirectToRoute('app_post');

        }
        return $this->render('post/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/post/delete/{id}", name="deletepost")
     */
    public function deletepost(Post $post): Response
    {
        $em = $this->getDoctrine()->getManager();
        foreach ($post->getCommentaires() as $comment){
            $em->remove($comment);
            $em->flush();


        }
        $em->remove($post);
        $em->flush();
        return $this->redirectToRoute('app_post');

    }
    /**
     * @Route("/update/post/{id}", name="updatepost")
     */
    public function updatepost(PostRepository $postRepository,Request $request,UserRepository $repository,Post $post): Response
    {

        $form = $this->createForm(PostType::class,$post);
        $form->handleRequest($request);
        $user = $repository->find(1);
        if($form->isSubmitted() && $form->isValid()){
            $post->setUser($user);
            $post->setDate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('app_post');




        }
        return $this->render('post/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/listp", name="post_pdf")
     */
    public function list(PostRepository $postRepository)
    {

// Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        $posts = $postRepository->findAll();

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('post/listp.html.twig', [
            'posts' => $posts,
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (inline view)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => true
        ]);

    }


    /**
     * @Route("/mobiledisplay", name="mobiledisplay")
     */

    public function recusermobile(Request $request, NormalizerInterface $Normalizer)
    {
        $em=$this->getDoctrine()->getManager ();
        $posts=$em->getRepository(Post::class)->findAll();
        $jsonContent=$Normalizer->normalize($posts, 'json',['groups'=>'post:read']);
        return new Response(json_encode ($jsonContent));
    }
    /**
     * @Route("/mobileadd", name="mobileadd")
     */
    public function addrecmobile (Request $request, NormalizerInterface $Normalizer,UserRepository $repository)
    {

        $em = $this->getDoctrine()->getManager();
        $posts = new Post();
        $date = new \DateTime('now');
        $user = $repository->find(1);

        $posts->setPost($request->get('post'));
        $posts->setImage($request->get('image'));
        $posts->setDate($date);
        $posts->setUser($user);


        $em->persist($posts);
        $em->flush();
        $jsonContent = $Normalizer->normalize($posts, 'json', ['groups' => 'post:read']);
        return new Response(json_encode($jsonContent));;
    }
    /**
     * @Route("/mobileupdate/{id}", name="mobileupdate")
     */
    public function updaterecmobile (Request $request, NormalizerInterface $Normalizer,$id,UserRepository $repository)
    {
        $em=$this->getDoctrine()->getManager();
        $posts=$em->getRepository(Post::class)->find($id);
        $date = new \DateTime('now');
        $user = $repository->find(1);
        $posts->setPost($request->get('post'));
        $posts->setImage($request->get('image'));
        $posts->setDate($date);
        $posts->setUser($user);

        $em->flush();
        $jsonContent=$Normalizer->normalize($posts, 'json', ['groups'=>'post:read']);
        return new Response("Reclammation updated successfully".json_encode($jsonContent));;
    }
    /**
     * @Route("/mobiledelete/{id}", name="mobiledelete")
     */
    public function deletemobile (Request $request, NormalizerInterface $Normalizer,$id)
    {
        $em=$this->getDoctrine()->getManager();
        $posts = $em->getRepository(Post::class)->find($id);
        foreach ($posts->getCommentaires() as $comment) {
            $em->remove($comment);
            $em->flush();
}


            $em->remove($posts);
            $em->flush();

        $jsonContent = $Normalizer->normalize($posts, 'json',['groups'=> 'post:read']);
        return new Response("Student deleted successfully".json_encode($jsonContent));
    }

}
