<?php
/**
 * Created by PhpStorm.
 * User: rameshbabu
 * Date: 18/11/16
 * Time: 12:51 AM
 */

class Api{

  private $kelin;
  private $mustache;
  private $db;

  public function __construct($kelin, $mustache, $db){
    $this->kelin = $kelin;
    $this->mustache = $mustache;
    $this->db = $db;
  }

  public function addRecipe($data){
    $date = date('Y-m-d H:i:s');
    $values = [
                'title' => $data['title'],
                'serving_count' => $data['serving_count'],
                'cook_time' => $data['cook_time'],
                'cook_temperature' => $data['cook_temperature'],
                'instructions' => isset($data['instructions']) ? $data['instructions'] : '',
                'chef_email' => $data['chef_email'],
                'created_at' => $date,
                'updated_at' => $date
              ];
    $stmt = $this->db->prepare("INSERT INTO recipe (title, serving_count, cook_time, cook_temperature, instructions, chef_email, created_at, updated_at) VALUES (:title, :serving_count, :cook_time, :cook_temperature, :instructions, :chef_email, :created_at, :updated_at)");
    $res = $stmt->execute($values);
    $recipeId = $this->db->lastInsertId();

    $ingredientQueryString = "INSERT INTO recipe_ingredient (recipe_id, ingredient_name, quantity, created_at, updated_at) VALUES ";
    $ingredientValues = [];
    $i = 0;
    $lnt = count($data['ingredients']);

    foreach($data['ingredients'] as $ingredient){
      $ingredientQueryString .= "(:recipe_id_$i, :ingredient_name_$i, :quantity_$i, :created_at_$i, :updated_at_$i)";
      if($lnt-1 != $i){
        $ingredientQueryString .= ",";
      }
      $ingredientValues["recipe_id_$i"] = $recipeId;
      $ingredientValues["ingredient_name_$i"] = $ingredient['name'];
      $ingredientValues["quantity_$i"] = $ingredient['quantity'];
      $ingredientValues["created_at_$i"] = $date;
      $ingredientValues["updated_at_$i"] = $date;
      $i = $i+1;
    }

    $stmt = $this->db->prepare($ingredientQueryString);
    $ingredientRes = $stmt->execute($ingredientValues);

    return $recipeId;
  }

  public function getAllRecipes(){
    $stmt = $this->db->prepare("SELECT (@curRow := @curRow + 1) AS row_number, r.*,ri.ingredient_name,ri.quantity FROM recipe r, recipe_ingredient ri, (SELECT @curRow := 0) rr where r.id=ri.recipe_id AND r.status='active' AND ri.status='active' GROUP BY ri.recipe_id ORDER BY r.id");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $data;
  }

  public function getRecipeIngredients($id){
    $stmt = $this->db->prepare("SELECT * FROM recipe_ingredient ri WHERE ri.recipe_id=:recipe_id AND ri.status='active' ORDER BY ri.updated_at");
    $stmt->execute(['recipe_id'=>$id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $data;
  }

  public function getRecipe($id){
    $stmt = $this->db->prepare("SELECT r.* FROM recipe r WHERE r.id = :id AND r.status='active'");
    $stmt->execute(["id" => $id]);
    $recipeData = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $this->db->prepare("SELECT ri.id, ri.ingredient_name, ri.quantity FROM recipe_ingredient ri WHERE ri.recipe_id = :id AND ri.status='active'");
    $stmt->execute(['id' => $id]);
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data = $recipeData;
    $data['ingredients'] = $ingredients;
    return $data;
  }

  public function updateRecipe($id, $data){
    $date = date('Y-m-d H:i:s');
    $values = [
      'title' => $data['title'],
      'serving_count' => $data['serving_count'],
      'cook_time' => $data['cook_time'],
      'cook_temperature' => $data['cook_temperature'],
      'instructions' => isset($data['instructions']) ? $data['instructions'] : '',
      'chef_email' => $data['chef_email'],
      'updated_at' => $date,
      'id' => $id
    ];

    $stmt = $this->db->prepare("UPDATE recipe SET title=:title, serving_count=:serving_count, cook_time=:cook_time, cook_temperature=:cook_temperature, instructions=:instructions, chef_email=:chef_email, updated_at=:updated_at WHERE id=:id");
    $insert = $stmt->execute($values);

    $stmt = $this->db->prepare("DELETE FROM recipe_ingredient WHERE recipe_id=:recipe_id");
    $stmt->execute(['recipe_id'=>$id]);

    $ingredientQueryString = "INSERT INTO recipe_ingredient (recipe_id, ingredient_name, quantity, created_at, updated_at) VALUES ";
    $ingredientValues = [];
    $i = 0;
    $lnt = count($data['ingredients']);

    foreach($data['ingredients'] as $ingredient){
      $ingredientQueryString .= "(:recipe_id_$i, :ingredient_name_$i, :quantity_$i, :created_at_$i, :updated_at_$i)";
      if($lnt-1 != $i){
        $ingredientQueryString .= ",";
      }
      $ingredientValues["recipe_id_$i"] = $id;
      $ingredientValues["ingredient_name_$i"] = $ingredient['name'];
      $ingredientValues["quantity_$i"] = $ingredient['quantity'];
      $ingredientValues["created_at_$i"] = $date;
      $ingredientValues["updated_at_$i"] = $date;
      $i = $i+1;
    }

    $stmt = $this->db->prepare($ingredientQueryString);
    $ingredientRes = $stmt->execute($ingredientValues);

    return $insert;
  }

  public function deleteRecipe($id){
    $stmt = $this->db->prepare("UPDATE recipe SET status=:status WHERE id=:id ");
    $updateRecipe = $stmt->execute(["status" =>'inactive', "id" => $id]);

    $stmt = $this->db->prepare("UPDATE recipe_ingredient SET status=:status WHERE recipe_id=:id ");
    $updateIngredient = $stmt->execute(["status" =>'inactive', "id" => $id]);
    return $updateRecipe;
  }

}