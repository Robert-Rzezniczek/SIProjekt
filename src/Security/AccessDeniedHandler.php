<?php

/**
 * AccessDenied Handler.
 */

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * AccessDeniedHandler class.
 */
class AccessDeniedHandler extends AbstractController implements AccessDeniedHandlerInterface
{
    /**
     * Translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Construct.
     *
     * @param TranslatorInterface $translator TranslatorInterface
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Handler for the access denied exception.
     *
     * @param Request               $request               HTTP Request
     * @param AccessDeniedException $accessDeniedException AccessDeniedException
     *
     * @return Response|null HTTP Response|null
     */
    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        $this->addFlash('error', $this->translator->trans('message.access_denied'));

        return $this->render('@404/error404.html.twig', [], new Response('', Response::HTTP_NOT_FOUND));
    }
}
