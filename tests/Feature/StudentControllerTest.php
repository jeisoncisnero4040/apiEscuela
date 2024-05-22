<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Usermodel;
use App\Models\CourseModel;


class StudentControllerTest extends TestCase
{
    use RefreshDatabase;

  
    public function it_can_create_a_student()
    {
        
        $user = Usermodel::factory()->create();
        $course = CourseModel::factory()->create();

        
        $data = [
            'user_id' => $user->id,
            'course_id' => $course->id,
        ];

        
        $response = $this->postJson('/api/students', $data);

        
        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'success',
                     'status' => 201,
                     'data' => [
                         'user_id' => $user->id,
                         'course_id' => $course->id,
                     ]
                 ]);

      
        $this->assertDatabaseHas('students', $data);
    }

    /** @test */
   /** @test */
public function it_returns_error_if_student_already_exists()
{
    // Create a user and a course
    $user = Usermodel::factory()->create();
    $course = CourseModel::factory()->create();

   
    $data = [
        'user_id' => $user->id,
        'course_id' => $course->id,
    ];

    
    $response = $this->postJson('/api/students', $data);

 
    $response->assertStatus(201);


    $response = $this->postJson('/api/students', $data);

    $response->assertStatus(400)
             ->assertJson([
                 'message' => 'failed',
                 'error' => 'Student already enrolled in this course',
                 'status' => 400,
                 'data' => []
             ]);
}
}
