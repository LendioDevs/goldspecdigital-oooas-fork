<?php

namespace GoldSpecDigital\ObjectOrientedOAS\Tests;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Contact;
use GoldSpecDigital\ObjectOrientedOAS\Objects\ExternalDocs;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Info;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\PathItem;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Paths;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Server;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Tag;
use GoldSpecDigital\ObjectOrientedOAS\OpenApi;

class ExampleTest extends TestCase
{
    public function test_example()
    {
        $tags = [
            Tag::create('Audits')->description('All the audits'),
        ];

        $contact = Contact::create(
            'Ayup Digital',
            'https://ayup.agency',
            'info@ayup.agency'
        );

        $info = Info::create('Core API Specification', 'v1')
            ->description('For using the Core Example App API')
            ->contact($contact);

        $exampleObject = Schema::object()->properties(
            Schema::string('id')->format(Schema::UUID),
            Schema::string('created_at')->format(Schema::DATE_TIME)
        )->required('id', 'created_at');
        $exampleResponse = Response::create(
            200,
            'OK',
            MediaType::json($exampleObject)
        );

        $listAudits = Operation::get($exampleResponse)
            ->tags('Audits')
            ->summary('List all audits')
            ->operationId('audits.index');

        $createAudit = Operation::post($exampleResponse)
            ->tags('Audits')
            ->summary('Create an audit')
            ->operationId('audits.store')
            ->requestBody(RequestBody::create(
                MediaType::json($exampleObject)
            ));

        $auditId = Schema::string('audit')->format(Schema::UUID);
        $format = Schema::string('format')->enum('json', 'ics')->default('json');
        
        $readAudit = Operation::get($exampleResponse)
            ->tags('Audits')
            ->summary('View an audit')
            ->operationId('audits.show')
            ->parameters(
                Parameter::path('audit', $auditId)->required(),
                Parameter::query('format', $format)->description('The format of the appointments')
            );

        $paths = Paths::create(
            PathItem::create('/audits', $listAudits, $createAudit),
            PathItem::create('/audits/{audit}', $readAudit)
        );

        $servers = [
            Server::create('https://api.example.com/v1'),
            Server::create('https://api.example.com/v2'),
        ];

        $security = ['OAuth2' => []];

        $externalDocs = ExternalDocs::create('https://github.com/RoyalBoroughKingston/cwk-api/wiki')
            ->description('GitHub Wiki');

        $openApi = OpenApi::create(OpenApi::VERSION_3_0_1, $info, $paths)
            ->servers(...$servers)
            ->security($security)
            ->tags(...$tags)
            ->externalDocs($externalDocs);

        var_dump($openApi->toJson());
    }
}
