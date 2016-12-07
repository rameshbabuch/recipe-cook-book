<?php
/**
 * Created by PhpStorm.
 * User: rameshbabu
 * Date: 6/12/16
 * Time: 9:56 PM
 */

require_once __DIR__ . '/vendor/autoload.php';
include 'lib/MysqlPdo.php';
include 'lib/Mailer.php';
include 'api.php';

$klein = new \Klein\Klein();
$m = new Mustache_Engine(array(
    'loader'          => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/views'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/views/partials'),
));

$settings = parse_ini_file("config.ini.php");
$pdo = new MysqlPdo($settings);
$db = $pdo->db;

$api = new Api($klein, $m, $db);

$mailer = new Mailer($settings);

$klein->respond('GET', '/', function ($request, $response, $service) use ($m, $api) {
  $recipes = $api->getAllRecipes();
  $template = 'partials/recipe-list';
  $service->render('views/index.phtml', array('mustache'=>$m, 'template' => $template, 'data'=>['recipes'=>$recipes]));
});

$klein->respond('GET', '/new-recipe', function ($request, $response, $service) use ($m) {
  $template = 'partials/new-recipe';
  $service->render('views/index.phtml', array('mustache'=>$m, 'template' => $template, 'data'=>[]));
});

$klein->respond('POST', '/submit-recipe', function ($request, $response, $service) use ($m, $api, $mailer) {
  $params = $request->params();
  $res = ['success'=> false, 'message'=>'Could not created recipe !'];
  $errorMessages = [];

  if(isset($params['title']) && !$service->validateParam('title')->notNull()) {
    $errorMessages[] = ['field'=>'title', 'message'=>'Invalid title'];
  }

  if(isset($params['serving_count']) && !$service->validateParam('serving_count')->notNull()) {
    $errorMessages[] = ['field'=>'serving_count', 'message'=>'Serving count should be at least 1'];
  }

  if(isset($params['cook_time']) && !$service->validateParam('cook_time')->notNull()) {
    $errorMessages[] = ['field'=>'cook_time', 'message'=>'Cook time should be valid time'];
  }

  if(isset($params['cook_temperature']) && !$service->validateParam('cook_temperature')->notNull()) {
    $errorMessages[] = ['field'=>'title', 'message'=>'Invalid title'];
  }

  if(
      !isset($params['ingredient_quantity']) || !isset($params['ingredient_name']) ||
      (count($params['ingredient_quantity']) != count($params['ingredient_name']))
  ) {
    $errorMessages[] = ['field'=>'ingredient_quantity', 'message'=>'At least one Ingredient required & should not empty'];
  }

  if(isset($params['chef_email']) && !$service->validateParam('chef_email')->notNull()) {
    $errorMessages[] = ['field'=>'chef_email', 'message'=>'Chef Email is required'];
  }

  if(!empty($errorMessages)) {
    $res['data'] = $errorMessages;
    $res['message'] = 'Invalid recipe data';
    return $response->json($res);
  }

  $values = $params;
  $values['ingredients'] = [];
  $i = 0;

  foreach($params['ingredient_name'] as $name){
    $ingredient = [];
    $ingredient['name'] = $name;
    $ingredient['quantity'] = $params['ingredient_quantity'][$i];
    $values['ingredients'][] = $ingredient;
    $i = $i+1;
  }

  if(isset($params['recipe_id']) && !empty($params['recipe_id'])){
    //update
    $recipeId = $params['recipe_id'];
    $resData = $api->updateRecipe($recipeId, $values);
    $res['success'] = true;
    $res['message'] = 'Recipe updated successfully';
    $res['data'] = ['recipe_id'=>$recipeId];

  } else {
    $recipeId = $api->addRecipe($values);
    //send email
    $subject = 'New Recipe Added';
    $message = 'Hi Dear, <br> New Recipe created for you, <br> check at <a href="http://localhost:8000">http://localhost:8000</a>';
    try{
      $mailer->send($values['chef_email'], $subject, $message);
    } catch (Exception $e){
      $res['message'] = 'Mail not sent.';
      return $response->json($res);
    }

    $res['success'] = true;
    $res['message'] = 'Recipe created successfully';
    $res['data'] = ['recipe_id'=>$recipeId];
  }

  return $response->json($res);
});


$klein->respond('GET', '/edit-recipe/[:recipe_id]', function ($request, $response, $service) use ($m, $api) {
  $recipeId = $request->params('recipe_id','');
  $recipeData = [];

  if(!empty($recipeId)){
    $recipeData  = $api->getRecipe($recipeId['recipe_id']);
  }
  $data = ['recipe'=>$recipeData];
  $template = 'partials/new-recipe';
  $service->render('views/index.phtml', array('mustache'=>$m, 'template' => $template, 'data'=>$data));

});

$klein->respond('DELETE', '/recipe/[:recipe_id]', function ($request, $response, $service) use ($m, $api) {
  $recipeId = $request->params('recipe_id','');
  $recipeData = 0;
  $res = ['success'=>false, 'message'=>'Could not delete recipe'];

  if(!empty($recipeId)){
    $recipeData  = $api->deleteRecipe($recipeId['recipe_id']);
  }

  if($recipeData > 0){
    $res['success'] = true;
    $res['message'] = 'Recipe deleted successfully';
  }

  return $response->json($res);

});

function sendEmail(){

}

$klein->dispatch();