<?php
/**
 * Created by PhpStorm.
 * User: rameshpaul
 * Date: 7/12/16
 * Time: 1:41 PM
 */
//require_once __DIR__ . '/vendor/autoload.php';

class ServicesTest extends \PHPUnit_Framework_TestCase
{
  private $recipeId;

  public function testListRecipe()
  {
    $client = new \GuzzleHttp\Client();

    $response = $client->request('GET', 'http://localhost:8000');

    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testAddRecipe()
  {
    $client = new \GuzzleHttp\Client();

    $data = array(
      'form_params' => array(
        'title' => 'Tomato Rice',
        'serving_count' => 5,
        'ingredient_name' => ['rice', 'tomato'],
        'ingredient_quantity' => ['200', '100'],
        'cook_time' => '00:30:00',
        'cook_temperature' => 100,
        'instructions' => 'Cook rice in 100 degrees, after 15 minutes add tomato and fry the rice. ',
        'chef_email' => 'chrb.rameshbabu@gmail.com'
      )
    );

    $response = $client->request('POST', 'http://localhost:8000/submit-recipe', $data);

    $this->assertEquals(200, $response->getStatusCode());
    $resData = (array) json_decode($response->getBody());
    $this->assertArrayHasKey('success', $resData);
    $this->assertEquals(true, $resData['success']);
    $this->assertGreaterThan(0, $resData['data']->recipe_id);
    $this->recipeId = $resData['data']->recipe_id;
  }

  public function testEditRecipe()
  {
    $client = new \GuzzleHttp\Client();

    $data = array(
      'form_params' => array(
        'title' => 'Tomato Rice edited',
        'serving_count' => 5,
        'ingredient_name' => ['rice', 'tomato'],
        'ingredient_quantity' => ['200', '100'],
        'cook_time' => '00:30:00',
        'cook_temperature' => 100,
        'instructions' => 'Cook rice in 100 degrees, after 15 minutes add tomato and fry the rice. ',
        'chef_email' => 'chrb.rameshbabu@gmail.com',
        'recipe_id' => $this->recipeId
      )
    );

    $response = $client->request('POST', 'http://localhost:8000/submit-recipe', $data);

    $this->assertEquals(200, $response->getStatusCode());
    $resData = (array) json_decode($response->getBody());
    $this->assertArrayHasKey('success', $resData);
    $this->assertEquals(true, $resData['success']);
    $this->assertGreaterThan(0, $resData['data']->recipe_id);
  }

  public function testDeleteRecipe()
  {
    $client = new \GuzzleHttp\Client();
    if(empty($this->recipeId)){
      $this->recipeId = 1;
    }

    $response = $client->request('DELETE', 'http://localhost:8000/recipe/'.$this->recipeId);

    $this->assertEquals(200, $response->getStatusCode());
    $resData = (array) json_decode($response->getBody());
    $this->assertArrayHasKey('success', $resData);
    $this->assertEquals(true, $resData['success']);
  }

}