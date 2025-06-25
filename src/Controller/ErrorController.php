<?php

/**
 * Error controller.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ErrorController.
 */
#[AsController]
class ErrorController extends AbstractController
{
    /**
     *  Show action.
     *
     * @param FlattenException $exception Flatten exception
     * @param Request          $request   Request
     *
     * @return Response HTTP response
     */
    #[Route(path: '/_error/{code}', name: 'app_error')]
    public function show(FlattenException $exception, Request $request): Response
    {
        $code = $exception->getStatusCode();

        return $this->render('404/error404.html.twig', [
            'status_code' => $code,
            'message' => $exception->getMessage(),
        ]);
    }
}
