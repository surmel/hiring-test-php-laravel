<?php

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('can attach tags to a post', function () {
    $post = Post::factory()->for($this->user)->create();
    $tag = Tag::factory()->create();

    $response = $this->postJson("/api/posts/{$post->id}/tags", [
        'tag_ids' => [$tag->id],
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('data.tags.0.id', $tag->id);

    $this->assertDatabaseHas('post_tag', [
        'post_id' => $post->id,
        'tag_id' => $tag->id,
    ]);
});

test('cannot attach a non-existent tag', function () {
    $post = Post::factory()->for($this->user)->create();

    $response = $this->postJson("/api/posts/{$post->id}/tags", [
        'tag_ids' => [999],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['tag_ids.0']);
});

test('can detach a tag from a post', function () {
    $post = Post::factory()->for($this->user)->create();
    $tag = Tag::factory()->create();
    $post->tags()->attach($tag->id);

    $response = $this->deleteJson("/api/posts/{$post->id}/tags/{$tag->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('post_tag', [
        'post_id' => $post->id,
        'tag_id' => $tag->id,
    ]);
});

test('post response includes attached tags', function () {
    $post = Post::factory()->for($this->user)->create();
    $tags = Tag::factory(2)->create();
    $post->tags()->attach($tags->pluck('id'));

    $response = $this->getJson("/api/posts/{$post->id}");

    $response->assertStatus(200);
    $response->assertJsonCount(2, 'data.tags');
});

test('can create a tag', function () {
    $response = $this->postJson('/api/tags', [
        'name' => 'Backend',
        'slug' => 'backend',
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.name', 'Backend');

    $this->assertDatabaseHas('tags', ['slug' => 'backend']);
});

test('cannot create a tag with a duplicate slug', function () {
    Tag::factory()->create(['slug' => 'backend']);

    $response = $this->postJson('/api/tags', [
        'name' => 'Backend Again',
        'slug' => 'backend',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
});

test('can update a tag', function () {
    $tag = Tag::factory()->create(['name' => 'Old name']);

    $response = $this->putJson("/api/tags/{$tag->id}", [
        'name' => 'New name',
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('data.name', 'New name');
});

test('can delete a tag', function () {
    $tag = Tag::factory()->create();

    $response = $this->deleteJson("/api/tags/{$tag->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
});
