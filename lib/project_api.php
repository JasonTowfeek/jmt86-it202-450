<?php
// jmt86 - 07/18/2026
// Searches TheMealDB and returns simplified meal data.

function search_meals(string $search, bool $use_sample, array &$errors): array
{
    if ($use_sample) {
        $result = api_sample_response("project-api-sample.json");
    } else {
        if (trim($search) === "") {
            $errors[] = "Please enter a meal name.";
            return [];
        }

        $result = api_get(
            "https://www.themealdb.com/api/json/v1/1/search.php",
            ["s" => trim($search)]
        );
    }

    $decoded = decode_api_response($result, "meals", $errors);

    if ($decoded === null) {
        return [];
    }

    $meals = [];

    foreach ($decoded["meals"] as $meal) {
        $meals[] = [
            "id" => $meal["idMeal"] ?? "",
            "name" => $meal["strMeal"] ?? "",
            "category" => $meal["strCategory"] ?? "",
            "cuisine" => $meal["strArea"] ?? "",
            "image" => $meal["strMealThumb"] ?? ""
        ];
    }

    return $meals;
}