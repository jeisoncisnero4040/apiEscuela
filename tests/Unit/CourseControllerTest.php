<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\CourseModel;
use App\Models\Usermodel;

class CourseControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test updating a course by ID.
     *
     * @return void
     */
    public function testUpdateCourseById()
    {
        
        $course = CourseModel::factory()->create();

       
        $teacher = Usermodel::factory()->create(['id_rol' => 2]); 


        $newData = [
            'teacher_id' => $teacher->id,

        ];


        $response = $this->patchJson("/api/courses/{$course->id}", $newData);

     
        $response->assertStatus(200);

        
        $response->assertJsonStructure([
            'message',
            'status',
            'data' => [                
                
                'teacher_id',
                
            ],
        ]);

        
        $response->assertJsonFragment($newData);

        
        $this->assertDatabaseHas('courses', $newData);
    }

    /**
     * Test updating a course by ID with invalid teacher ID.
     *
     * @return void
     */
    public function testUpdateCourseWithInvalidTeacherId()
    {
         
        $course = CourseModel::factory()->create();

        
        $newData = [
            'teacher_id' => 3,  
            
        ];

        
        $response = $this->patchJson("/api/courses/{$course->id}", $newData);

        
        $response->assertStatus(400);

       
        $response->assertJsonStructure([
            'message',
            'error',
            'status',
            'data',
        ]);

         
    }
}
