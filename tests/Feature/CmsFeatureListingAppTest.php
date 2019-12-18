<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserDetails;
use App\Models\Admin;
use App\Models\MarketplaceFeaturedListing;
use Tests\TestCase;


class CmsFeatureListingAppTest extends TestCase
{
    protected $token;
    protected $tokenAdmin;
    protected $testId;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        
        // USER TABLER CONTEXT
        $admin = factory(Admin::class)->create([
                'email' => 'cms@test.com',
                'password' => bcrypt('123456')]
        );

        $response_admin = $this->post('api/admin/login', [
            'email' => 'cms@test.com',
            'password' => '123456',
        ]);
 
        $this->tokenAdmin = $response_admin->json('token');
      
    }

    public function test_list_marketplace_feature_listing_201()
    {
         factory(MarketplaceFeaturedListing::class, 20)->create();

        $response = $this->json('GET',
            'api/cms/marketplace-featured-listing?token=' . $this->tokenAdmin);

        $response->assertStatus(200);
    }

   
}
