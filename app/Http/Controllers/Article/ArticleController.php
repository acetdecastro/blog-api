<?php

namespace App\Http\Controllers\Article;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Resources\Article as ArticleResource;
use App\Article;

class ArticleController extends Controller
{
    public function index ()
    {
        $this->authorize('viewAny', Article::class);

        return ArticleResource::collection(request()->user()->articles);
    }

    public function store (ArticleRequest $request)
    {
        $this->authorize('create', Article::class);

        $article = request()->user()->articles()->create($request->validated());

        return (new ArticleResource($article))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
    
    public function show (Article $article)
    {      
        $this->authorize('view', $article);

        return new ArticleResource($article);
    }

    public function update (ArticleRequest $request, Article $article)
    {
        $this->authorize('update', $article);

        $article->update($request->validated());

        return (new ArticleResource($article))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function destroy (Article $article)
    {
        $this->authorize('delete', $article);

        $article->delete();

        return response()->json(['success' => true], Response::HTTP_OK);
    }
}
