<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachTagsRequest;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PostController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $posts = Post::with(['user', 'tags'])->paginate(15);
        return PostResource::collection($posts);
    }

    /**
     * @param Post $post
     * @return PostResource
     */
    public function show(Post $post): PostResource
    {
        return new PostResource($post->load(['user', 'tags']));
    }

    /**
     * @param StorePostRequest $request
     * @return JsonResponse
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $post = $user->posts()->create($request->validated());

        return (new PostResource($post->load('user')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param UpdatePostRequest $request
     * @param Post $post
     * @return PostResource
     */
    public function update(UpdatePostRequest $request, Post $post): PostResource
    {
        $post->update($request->validated());

        return new PostResource($post->load('user'));
    }

    /**
     * @param Post $post
     * @return Response
     */
    public function destroy(Post $post): Response
    {
        $post->delete();

        return response()->noContent();
    }

    /**
     * @param AttachTagsRequest $request
     * @param Post $post
     * @return PostResource
     */
    public function attachTags(AttachTagsRequest $request, Post $post): PostResource
    {
        /** @var array<int> $tagIds */
        $tagIds = $request->validated('tag_ids');

        $post->tags()->syncWithoutDetaching($tagIds);

        return new PostResource($post->load('tags'));
    }

    /**
     * @param Post $post
     * @param Tag $tag
     * @return Response
     */
    public function detachTag(Post $post, Tag $tag): Response
    {
        $post->tags()->detach($tag->id);

        return response()->noContent();
    }
}
