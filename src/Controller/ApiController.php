<?php

namespace App\Controller;


use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Encoder\JsonEncoder;


class ApiController extends AbstractController
{

    protected $filename = '../base.csv';

    /**
     * @Route("/api", name="api_api")
     */
    public function api(Request $request)
    {
        //here function getContentType returns json instead of application/json
        if ('json' !== $request->getContentType()) {
            return new Response('', 415);
        }

        $uri = $request->getUri();
        $allData = $this->getMainData($uri . '/');

        return new JsonResponse($allData);

    }

    /**
     * @Route("/api/my-info", name="api_info")
     */
    public function apiInfo(Request $request)
    {
        //here function getContentType returns json instead of application/json
        if ('json' !== $request->getContentType()) {
            return new Response('', 415);
        }

        $data = $this->encodeUserDataToJson($request);

        $response = new JsonResponse($data);

        try {
            file_put_contents('../my-info-log2.json', $data.PHP_EOL, FILE_APPEND);
        }
        catch(IOException $e) {
        }

        return $response;
    }

    /**
     * @Route("/api/{sku}", name="api_product")
     */
    public function product(Request $request, string $sku)
    {
        //here function getContentType returns json instead of application/json
        if ('json' !== $request->getContentType()) {
            return new Response('', 415);
        }

        $productData = $this->getProductBySky($sku);
        return new JsonResponse($productData);
    }




    private function getProductBySky(string $sku)
    {
        $handle = fopen($this->filename, "r");
        $fieldNameArray = fgetcsv($handle);
        while (($data = fgetcsv($handle)) !== FALSE) {
            if ($data[0] == $sku) {
                $resultArray = [];
                foreach ($fieldNameArray as $key => $value) {
                    $resultArray[$value] = $data[$key];
                }

                return $resultArray;
            }
        }

        return NULL;
    }

    private function getMainData(string $uri)
    {
        $handle = fopen($this->filename, "r");

        fgetcsv($handle);//Adding this line will skip the reading of th first line from the csv file and the reading process will begin from the second line onwards

        $resultArray = [];
        while (($line = fgetcsv($handle)) !== FALSE) {
                $resultArray[] = $uri . $line[0];
        }

        return $resultArray;
    }

    private function encodeUserDataToJson(Request $request)
    {
        $userData = array(
            'ip' => $request->getClientIp(),
            'language' => $request->getLocale(),
            'browser' => $request->headers->get('User-Agent')
        );

        $jsonEncoder = new JsonEncoder();
        return $jsonEncoder->encode($userData, $format = 'json');
    }
}
