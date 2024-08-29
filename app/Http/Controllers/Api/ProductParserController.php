<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParsedProduct;
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

            $title = $this->extractTitleWithRegex($data);
            $price = $this->extractPriceWithRegex($data);
            $images = $this->extractImagesWithRegex($data);

            if (empty($title) && empty($price) && empty($images)) {
                return response()->json(['error' => 'Failed to parse the product data'], 500);
            }

            // Save the parsed data to the database
            $this->saveParsedDataToDatabase($url, $title, $price, $images);

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

    private function extractTitleWithRegex($data)
    {
        $patterns = [
            '/<h1[^>]*>(.*?)<\/h1>/',              // Matches <h1> tags
            '/<title>(.*?)<\/title>/',             // Matches <title> tags
            '/<meta property="og:title" content="(.*?)"/', // Matches Open Graph <meta> title tags
            '/<meta name="title" content="(.*?)"/',        // Matches <meta name="title">
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $data, $matches)) {
               return  strip_tags(
                   trim($matches[1])
               );

            }
        }

        return null;
    }

    private function extractPriceWithRegex($data): ?string
    {
        $patterns = [
            '/<span[^>]*class=["\']price["\'][^>]*>(.*?)<\/span>/',
            '/<span[^>]*class=["\']prc-dsc["\'][^>]*>(.*?)<\/span>/',
            '/<meta property="product:price:amount" content="(.*?)"/',
            '/<meta name="price" content="(.*?)"/',
            '/\$([0-9,]+\.?[0-9]{0,2})/', // Matches prices like $99.99 or $99
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $data, $matches)) {
                return trim(strip_tags($matches[1]));
            }
        }

        return null;
    }

    private function extractImagesWithRegex($data)
    {
        $pattern = '/<img[^>]*src=["\'](.*?)["\']/';
        preg_match_all($pattern, $data, $matches);
        return $matches[1] ?? [];
    }

    private function saveParsedDataToDatabase($url, $title, $price, $images)
    {
        // Check if the product already exists based on the URL
        $existingProduct = ParsedProduct::where('url', $url)->first();

        if ($existingProduct) {
            // Update the existing record if found
            $existingProduct->title = $title;
            $existingProduct->price = $price;
            $existingProduct->images = json_encode($images);
            $existingProduct->save();
        } else {
            // Create a new record if not found
            ParsedProduct::create([
                'url' => $url,
                'title' => $title,
                'price' => $price,
                'images' => json_encode($images),
            ]);
        }
    }

    public function handleParseRequest(Request $request)
    {
        $url = $request->input('url');

        $data = file_get_contents($url);

        // Use regex to extract the title, price, and images
        $title = $this->extractTitleWithRegex($data);
        $price = $this->extractPriceWithRegex($data);
        $images = $this->extractImagesWithRegex($data);

        // Return the view with the parsed data
        return view('parse-form', [
            'title' => $title ?: 'Title not found',
            'price' => $price ?: 'Price not found',
            'images' => $images
        ]);
    }
    //TODO  (make parse data save database and pay attention duplication)
    //TODO  (make HTML side HTML structure can change any time)
}
