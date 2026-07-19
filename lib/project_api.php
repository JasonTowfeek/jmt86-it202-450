<?php
// UCID: mp2446
// Date: 07/18/2026
// Project API wrapper for the Open Library Search API.

function get_open_library_books(string $searchTerm): array
{
    $searchTerm = trim($searchTerm);

    if ($searchTerm === "") {
        return [
            "success" => false,
            "message" => "Please enter a book search term.",
            "books" => []
        ];
    }

    $url = "https://openlibrary.org/search.json?q=" . urlencode($searchTerm) . "&limit=10";

    $response = @file_get_contents($url);

    if ($response === false) {
        error_log("Open Library API request failed.");

        return [
            "success" => false,
            "message" => "The book service is currently unavailable.",
            "books" => []
        ];
    }

    $data = json_decode($response, true);

    if (!is_array($data) || !isset($data["docs"]) || !is_array($data["docs"])) {
        error_log("Open Library API returned an unexpected response.");

        return [
            "success" => false,
            "message" => "The book service returned invalid data.",
            "books" => []
        ];
    }

    $books = [];

    foreach ($data["docs"] as $book) {
        $books[] = [
            "title" => $book["title"] ?? "Unknown Title",
            "author" => isset($book["author_name"])
                ? implode(", ", $book["author_name"])
                : "Unknown Author",
            "publish_year" => $book["first_publish_year"] ?? "Unknown",
            "cover_id" => $book["cover_i"] ?? null
        ];
    }

    return [
        "success" => true,
        "message" => "Books loaded successfully.",
        "books" => $books
    ];
}
