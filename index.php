

<?php

    require 'vendor/autoload.php';
    require "random_string.php";
    use MicrosoftAzure\Storage\Blob\BlobRestProxy;
    use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
    use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
    use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
    use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

  # Setup a specific instance of an Azure::Storage::Client
    require 'access.php';

    // Create blob client.
    $blobClient = BlobRestProxy::createBlobService($connectionString);

    # Create the BlobService that represents the Blob service for the storage account
    $createContainerOptions = new CreateContainerOptions();

    $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);

    // Set container metadata.
    $createContainerOptions->addMetaData("key1", "value1");
    $createContainerOptions->addMetaData("key2", "value2");

    //$date = date("D M d, Y G:i");
    $containerName = "mag".generateRandomString();

    $fileToUpload = "test.txt";

    try {
    // Create container.
    $blobClient->createContainer($containerName, $createContainerOptions);

    // Getting local file so that we can upload it to Azure
    $myfile = fopen($fileToUpload, "r") or die("Unable to open file!");
    fclose($myfile);

    # Upload file as a block blob
    echo "Uploading BlockBlob: ".PHP_EOL;
    echo $fileToUpload;
    echo "<br />";

    $content = fopen($fileToUpload, "r");

    //Upload blob
    $blobClient->createBlockBlob($containerName, $fileToUpload, $content);

    // List blobs.
    $listBlobsOptions = new ListBlobsOptions();
    $listBlobsOptions->setPrefix("Mag");

    echo "These are the blobs present in the container: ";

    do{
        $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
        foreach ($result->getBlobs() as $blob)
        {
            echo $blob->getName().": ".$blob->getUrl()."<br />";
        }

        $listBlobsOptions->setContinuationToken($result->getContinuationToken());
    } while($result->getContinuationToken());
    echo "<br />";

    // Get blob.
    echo "This is the content of the blob uploaded: ";
    $blob = $blobClient->getBlob($containerName, $fileToUpload);
    fpassthru($blob->getContentStream());
    echo "<br />";
}

    catch(ServiceException $e){
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        // http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
    catch(InvalidArgumentTypeException $e){
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        // http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
?>
