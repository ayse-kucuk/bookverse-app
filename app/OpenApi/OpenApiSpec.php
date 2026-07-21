<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Bookverse API',
    description: 'Bookverse sosyal okuma uygulaması REST API dokümantasyonu. Token almak için /api/login veya /api/register kullanın, ardından Authorize ile Bearer token ekleyin.'
)]
#[OA\Server(
    url: '/api',
    description: 'API v1'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum',
    description: 'Laravel Sanctum personal access token'
)]
#[OA\Tag(name: 'Auth', description: 'Kimlik doğrulama')]
#[OA\Tag(name: 'Books', description: 'Kitaplar')]
#[OA\Tag(name: 'Posts', description: 'Paylaşımlar')]
#[OA\Tag(name: 'Users', description: 'Kullanıcılar')]
#[OA\Tag(name: 'Search', description: 'Arama')]
#[OA\Tag(name: 'Notifications', description: 'Bildirimler')]
class OpenApiSpec
{
}
