<?php

/**
 * ItemSearchInputFiltersDto resolver.
 */

namespace App\Resolver;

use App\Dto\ItemSearchInputFiltersDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * ItemSearchFiltersDtoResolver class.
 */
class ItemSearchFiltersDtoResolver implements ValueResolverInterface
{
    /**
     * Returns the possible value(s).
     *
     * @param Request          $request  HTTP Request
     * @param ArgumentMetadata $argument Argument metadata
     *
     * @return iterable iterable
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if (!$argumentType || !is_a($argumentType, ItemSearchInputFiltersDto::class, true)) {
            return [];
        }

        $title = $request->query->get('title');
        $rating = $request->query->has('rating') && is_numeric($request->query->get('rating')) ? (int) $request->query->get('rating') : null;
        $categoryId = $request->query->has('categoryId') && is_numeric($request->query->get('categoryId')) ? (int) $request->query->get('categoryId') : null;
        $tagId = $request->query->has('tagId') && is_numeric($request->query->get('tagId')) ? (int) $request->query->get('tagId') : null;

        return [new ItemSearchInputFiltersDto($title, $rating, $categoryId, $tagId)];
    }
}
