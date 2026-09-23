<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachTagsRequest;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Response;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with(['user', 'tags'])->paginate(15);
        return PostResource::collection($posts);
    }

    public function show(Post $post)
    {
        return new PostResource($post->load(['user', 'tags']));
    }

    public function store(StorePostRequest $request)
    {
        $post = auth()->user()->posts()->create($request->validated());

        return (new PostResource($post->load('user')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update($request->validated());

        return new PostResource($post->load('user'));
    }

    public function destroy(Post $post)
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
        $post->tags()->syncWithoutDetaching($request->validated('tag_ids'));

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
