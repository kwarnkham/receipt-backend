<?php

namespace Tests\Feature;

use App\Enums\ResponseStatus;
use App\Models\Picture;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PictureTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $user2;
    private $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user2 = User::factory()->create();
        $this->admin = User::factory()->has(Role::factory(['name' => 'admin']))->create();
    }

    public function test_save_picture()
    {
        $response = $this->actingAs($this->admin)->postJson('/api/picture', ['user_id' => $this->user->id, 'picture' => UploadedFile::fake()->image('test.jpg'), 'type' => 1]);

        $response->assertCreated();
        $this->assertDatabaseCount('pictures', 1);
        $picture = Picture::find($response->json()['id']);
        $response = Http::get($picture->url());
        $this->assertTrue($response->ok());
        $this->assertTrue($picture->deleteFromCloud());
    }

    public function test_only_admin_can_save_picture()
    {
        $response = $this->actingAs($this->user)->postJson('/api/picture', ['user_id' => $this->user->id, 'picture' => UploadedFile::fake()->image('test.jpg'), 'type' => 2]);
        $response->assertStatus(ResponseStatus::UNAUTHORIZED->value);
    }

    public function test_post_picture_if_exists_replace()
    {
        $response = $this->actingAs($this->admin)->postJson('/api/picture', ['user_id' => $this->user->id, 'picture' => UploadedFile::fake()->image('test.jpg'), 'type' => 1]);

        $response->assertCreated();
        $this->assertDatabaseCount('pictures', 1);
        $picture = Picture::find($response->json()['id']);
        $response = Http::get($picture->url());
        $this->assertTrue($response->ok());


        $response = $this->actingAs($this->admin)->postJson('/api/picture', ['user_id' => $this->user->id, 'picture' => UploadedFile::fake()->image('test.jpg'), 'type' => 1]);

        $response->assertCreated();
        $this->assertDatabaseCount('pictures', 1);
        $picture2 = Picture::find($response->json()['id']);
        $response = Http::get($picture2->url());
        $this->assertTrue($response->ok());
        $this->assertTrue($picture2->deleteFromCloud());
        $response = Http::get($picture->url());
        $this->assertFalse($response->ok());
    }

    public function test_upload_picture_for_public()
    {
        Storage::fake('local');
        $response = $this->actingAs($this->user)->postJson('api/picture/public', [
            'picture' => UploadedFile::fake()->image('test.jpg'),
        ]);

        $response->assertOk();
        Storage::disk('local')->assertExists($response->json());
    }
}
