<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use phpQuery;


class ProductParserController extends Controller
{
    public function showParseForm()
    {
        return view('parse-form'); // Returns the view to display the form
    }

    public function getParsedData(Request $request)
    {
        $url = $request->input('url');

        // Validate the URL input
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return response()->json(['error' => 'Invalid URL provided'], 400);
        }

        try {
            $data = file_get_contents($url);

            if ($data === false) {
                return response()->json(['error' => 'Failed to retrieve the content from the URL'], 500);
            }

            $pq = phpQuery::newDocumentHTML($data);

            $title = $pq->find('h1.pr-new-br')->text();
            $price = $pq->find('.prc-dsc')->text();
            $imgs = $pq->find('.gallery-container img');
            $images = [];
            foreach ($imgs as $img) {
                $images[] = pq($img)->attr('src');
            }
            if (empty($title) && empty($price) && empty($images)) {
                return response()->json(['error' => 'Failed to parse the product data'], 500);
            }

            // Prepare the JSON response
            $response = [
                'title' => $title ?: 'Title not found',
                'price' => $price ?: 'Price not found',
                'images' => $images,
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function handleParseRequest(Request $request)
    {
        $url = $request->input('url');

        $data = file_get_contents($url);
        $pq = phpQuery::newDocumentHTML($data);

        $title = $pq->find('h1.pr-new-br')->text();
        $price = $pq->find('.prc-dsc')->text();
        $imgs = $pq->find('.gallery-container img');
        $images = [];
        foreach ($imgs as $img) {
            $images[] = pq($img)->attr('src');
        }

        // Return the view with the parsed data
        return view('parse-form', [
            'title' => $title,
            'price' => $price,
            'images' => $images
        ]);

        if ($request->has('show_json')) {
            return response()->json($response);
        }

        // Otherwise, return the view with the parsed data
        return view('parse-form', $response);

    }

}
