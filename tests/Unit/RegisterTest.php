<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RegisterTest extends TestCase
{
    /**
     * Test register endpoint with valid data.
     *
     * @return void
     */
    public function test_register_endpoint_with_valid_data()
    {
        Storage::fake('public'); 

        $userData = [
            'name' => 'John Doe',
            'email' => 'john11@example.com',
            'id_rol' => 1,
            'password' => 'password',
            'image_url' => UploadedFile::fake()->image('picachu.jpg') 
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'status',
                     'data' => [
                         'user' => [
                             'id',
                             'name',
                             'email',
                             'id_rol',
                             'image_url',
                             'created_at',
                             'updated_at',
                         ],
                         'token',
                     ],
                 ]);

         
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'id_rol' => $userData['id_rol'],
            'image_url' => 'images/' . $userData['email'] . '.jpg',
        ]);
    }

     
}
