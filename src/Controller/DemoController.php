<?php

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class DemoController extends Controller
{

    /**
     * @Route("/demo", name="demo")
     * @Method("GET")
     */
    public function index()
    {
        // return $this->json([
        //     'message' => 'Welcome to your new controller!',
        //     'path' => 'src/Controller/DemoController.php',
        // ]);
        return new Response('Demo string');
    }
}