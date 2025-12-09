<?php

function apiResourceCollection($resource, $collection , $message = null)
{
    // if $collection is paginated
    if (method_exists($collection, 'items')) {
        return response()->json([
            'data' => $resource::collection($collection->items()),
            'meta' => [
                'current_page' => $collection->currentPage(),
                'last_page' => $collection->lastPage(),
                'per_page' => $collection->perPage(),
                'total' => $collection->total(),
            ],
            'links' => [
                'first' => $collection->url(1),
                'last'  => $collection->url($collection->lastPage()),
                'prev'  => $collection->previousPageUrl(),
                'next'  => $collection->nextPageUrl(),
            ],
            'message' => $message ?? null,
        ]);
    }

    return response()->json([
        'data' => $resource::collection($collection),
        'message' => $message ?? null,
    ]);
}

function apiResource($resource, $model, $status = 200, $message = null)
{
    return response()->json([
        'data' => new $resource($model),
        'message' => $message ?? null,
    ], $status);
}
