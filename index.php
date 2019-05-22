<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Dicoding Cognitive App (My Submission)</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
  </head>
  <body>
    <h1>Image Recognition</h1> <hr>

    <h3>Upload File</h3>
    <form action="index.php" method="post" enctype="multipart/form-data">
      <input type="file" name="filetoupload">
      <input type="submit" value="Upload">
    </form>
    <br>
    <hr>

<?php

require_once 'vendor/autoload.php';
require_once "./random_string.php";
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

// if (isset($_FILES['filetoupload'])) {
if ( move_uploaded_file( $_FILES['filetoupload']['tmp_name'] , basename($_FILES['filetoupload']['name']) )) {
  $connectionString = "DefaultEndpointsProtocol=https;AccountName=".getenv('ACCOUNT_NAME').";AccountKey=".getenv('ACCOUNT_KEY');
  // Create blob client.
  $blobClient = BlobRestProxy::createBlobService($connectionString);

  // $fileToUpload = $_FILES['filetoupload']['tmp_name'];
  $fileToUpload = $_FILES['filetoupload']['name'];
  // $fileName = $_FILES['filetoupload']['name'];

  $createContainerOptions = new CreateContainerOptions();
  $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);

  $createContainerOptions->addMetaData("key1", "value1");
  $createContainerOptions->addMetaData("key2", "value2");
  $containerName = "blockblobs".generateRandomString();

  try {
    // Create container.
    $blobClient->createContainer($containerName, $createContainerOptions);
    // Getting local file so that we can upload it to Azure
    $myfile = fopen($fileToUpload, "w") or die("Unable to open file!");
    fclose($myfile);

    # Upload file as a block blob
    echo "Uploading BlockBlob: ".PHP_EOL;
    echo $fileToUpload;
    echo "<br />";

    // $content = fopen($fileToUpload, "r");
    $blob_content = file_get_contents($fileToUpload);
    //Upload blob
    $blobClient->createBlockBlob($containerName, $fileToUpload, $blob_content);
    // Get blob.
    echo "This is the content of the blob uploaded: ";
    $blob = $blobClient->getBlob($containerName, $fileToUpload);
    fpassthru($blob->getContentStream());
    echo $blob->getName().": ".$blob->getUrl()."<br />";
    echo "<br />";
  }
  catch(ServiceException $e){
    $code = $e->getCode();
    $error_message = $e->getMessage();
    echo $code.": ".$error_message."<br />";
  }
  catch(InvalidArgumentTypeException $e){
    $code = $e->getCode();
    $error_message = $e->getMessage();
    echo $code.": ".$error_message."<br />";
  }
}

?>

  <script type="text/javascript">
    function processImage() {
      var subscriptionKey = "8abe030dc91740a0b48c93d41bea4e55";
      var uriBase = "https://southeastasia.api.cognitive.microsoft.com/vision/v2.0/analyze";

      var params = {
          "visualFeatures": "Categories,Description,Color",
          "details": "",
          "language": "en",
      };

      // Display the image.
      var sourceImageUrl = document.getElementById("inputImage").value;
      document.querySelector("#sourceImage").src = sourceImageUrl;

      // Make the REST API call.
      $.ajax({
          url: uriBase + "?" + $.param(params),
          beforeSend: function(xhrObj){
              xhrObj.setRequestHeader("Content-Type","application/json");
              xhrObj.setRequestHeader(
                  "Ocp-Apim-Subscription-Key", subscriptionKey);
          },
          type: "POST",
          data: '{"url": ' + '"' + sourceImageUrl + '"}',
        })
        .done(function(data) {
          $("#responseTextArea").val(JSON.stringify(data, null, 2));
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
          // Display error message.
          var errorString = (errorThrown === "") ? "Error. " :
              errorThrown + " (" + jqXHR.status + "): ";
          errorString += (jqXHR.responseText === "") ? "" :
              jQuery.parseJSON(jqXHR.responseText).message;
          alert(errorString);
        });

    };
  </script>
  </body>
</html>
