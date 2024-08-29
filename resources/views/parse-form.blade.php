<!DOCTYPE html>
<html>
<head>
    <title>URL Parser</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .result {
            margin-top: 20px;
        }

        .result img {
            max-height: 200px;
            margin: 10px;
        }
    </style>
<script>
    function showJson() {
    var url = document.getElementById('url').value;
    if (url) {
    var apiUrl = '/api/parse-url?url=' + encodeURIComponent(url);
    window.location.href = apiUrl;
    } else {
    alert('Please enter a URL.');
    }
    }
    </script>

</head>
<body>
<div class="container">
    <h1>URL Parser</h1>
    <form action="/parse" method="POST">
        @csrf
        <label for="url">Enter URL:</label>
        <input type="text" id="url" name="url" required placeholder="https://example.com">
        <input type="submit" value="Parse">
        <button type="button" class="json-button" onclick="showJson()">Show as JSON</button>
    </form>

    @isset($title)
        <div class="result">
            <h2>Parsed Results:</h2>
            <p><strong>Title:</strong> {{ $title }}</p>
            <p><strong>Price:</strong> {{ $price }}</p>
            <div>
                <strong>Images:</strong>
                @foreach ($images as $image)
                    <img src="{{ $image }}" alt="Parsed Image">
                @endforeach
            </div>
        </div>
    @endisset
</div>
</body>
</html>
