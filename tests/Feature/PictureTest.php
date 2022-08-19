<?php

namespace Tests\Feature;

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

        $response->assertStatus(201);
        $this->assertDatabaseCount('pictures', 1);
        $picture = Picture::find($response->json()['id']);
        $response = Http::get($picture->url());
        $this->assertTrue($response->ok());
        $this->assertTrue(Storage::disk('s3')->delete($picture->name));
    }
}
