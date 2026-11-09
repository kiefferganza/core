<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rakutentech\LaravelRequestDocs\Controllers\LaravelRequestDocsController;
use Rakutentech\LaravelRequestDocs\LaravelRequestDocs;
use Rakutentech\LaravelRequestDocs\LaravelRequestDocsToOpenApi;

class DocsController extends LaravelRequestDocsController
{
    private LaravelRequestDocs $laravelRequestDocs;

    private LaravelRequestDocsToOpenApi $laravelRequestDocsToOpenApi;

    public function __construct(LaravelRequestDocs $laravelRequestDoc, LaravelRequestDocsToOpenApi $laravelRequestDocsToOpenApi)
    {
        $this->laravelRequestDocs = $laravelRequestDoc;
        $this->laravelRequestDocsToOpenApi = $laravelRequestDocsToOpenApi;
    }

    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function api(Request $request): JsonResponse
    {
        $showGet = ! $request->has('showGet') || filter_var($request->input('showGet'), FILTER_VALIDATE_BOOLEAN);
        $showPost = ! $request->has('showPost') || filter_var($request->input('showPost'), FILTER_VALIDATE_BOOLEAN);
        $showPut = ! $request->has('showPut') || filter_var($request->input('showPut'), FILTER_VALIDATE_BOOLEAN);
        $showPatch = ! $request->has('showPatch') || filter_var($request->input('showPatch'), FILTER_VALIDATE_BOOLEAN);
        $showDelete = ! $request->has('showDelete') || filter_var($request->input('showDelete'), FILTER_VALIDATE_BOOLEAN);
        $showHead = ! $request->has('showHead') || filter_var($request->input('showHead'), FILTER_VALIDATE_BOOLEAN);

        // Get a list of Doc with route and rules information.
        // If user defined `Route::match(['get', 'post'], 'uri', ...)`,
        // only a single Doc will be generated.
        $docs = $this->laravelRequestDocs->getDocs(
            $showGet,
            $showPost,
            $showPut,
            $showPatch,
            $showDelete,
            $showHead,
        );

        // Loop and split Doc by the `methods` property.
        // `Route::match([...n], 'uri', ...)` will generate n number of Doc.
        $docs = $this->laravelRequestDocs->splitByMethods($docs);
        $docs = $this->laravelRequestDocs->sortDocs($docs, $request->input('sort'));
        $docs = $this->laravelRequestDocs->groupDocs($docs, $request->input('groupby'));

        if ($request->input('openapi')) {
            return response()->json(
                $this->laravelRequestDocsToOpenApi->openApi($docs->all())->toArray(),
                Response::HTTP_OK,
                [
                    'Content-type' => 'application/json; charset=utf-8',
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
        }

        return response()->json(
            $docs->values()->toArray(),
            Response::HTTP_OK,
            [
                'Content-type' => 'application/json; charset=utf-8',
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
}
