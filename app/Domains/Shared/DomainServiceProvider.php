<?php

namespace App\Domains\Shared;

use App\Domains\Administration\Models\AuditLog;
use App\Domains\Advertising\Models\MarketingCampaign;
use App\Domains\Analytics\Models\GeneratedReport;
use App\Domains\Analytics\Models\PropertyValuation;
use App\Domains\Analytics\Models\ReportTemplate;
use App\Domains\Booking\Models\AccommodationBooking;
use App\Domains\CRM\Models\CrmActivity;
use App\Domains\CRM\Models\Lead;
use App\Domains\Development\Models\DevelopmentBuilding;
use App\Domains\Development\Models\DevelopmentProject;
use App\Domains\Development\Models\DevelopmentUnit;
use App\Domains\Development\Models\PaymentPlan;
use App\Domains\Development\Models\PaymentPlanInstallment;
use App\Domains\Engagement\Models\Conversation;
use App\Domains\Engagement\Models\ConversationParticipant;
use App\Domains\Engagement\Models\Inquiry;
use App\Domains\Engagement\Models\Message;
use App\Domains\Engagement\Models\MessageAttachment;
use App\Domains\Engagement\Models\Offer;
use App\Domains\Engagement\Models\OfferEvent;
use App\Domains\Engagement\Models\Viewing;
use App\Domains\GIS\Models\MapFeature;
use App\Domains\GIS\Models\PropertySpatialData;
use App\Domains\Identity\Models\ApiToken;
use App\Domains\Identity\Models\Permission;
use App\Domains\Identity\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Listing\Models\Collection;
use App\Domains\Listing\Models\CollectionProperty;
use App\Domains\Listing\Models\Favorite;
use App\Domains\Listing\Models\Listing;
use App\Domains\Listing\Models\ListingFlag;
use App\Domains\Listing\Models\ListingModeration;
use App\Domains\Listing\Models\ListingSearchIndex;
use App\Domains\Location\Models\City;
use App\Domains\Location\Models\Country;
use App\Domains\Location\Models\Kebele;
use App\Domains\Location\Models\Location;
use App\Domains\Location\Models\LocationGeometry;
use App\Domains\Location\Models\LocationTranslation;
use App\Domains\Location\Models\Neighborhood;
use App\Domains\Location\Models\Region;
use App\Domains\Location\Models\SubCity;
use App\Domains\Location\Models\Woreda;
use App\Domains\Location\Models\Zone;
use App\Domains\Notification\Models\NotificationPreference;
use App\Domains\Organization\Models\Agent;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Developer;
use App\Domains\Organization\Models\Host;
use App\Domains\Organization\Models\Organization;
use App\Domains\Organization\Models\OrganizationMember;
use App\Domains\Organization\Models\Professional;
use App\Domains\Organization\Models\Team;
use App\Domains\Property\Models\Amenity;
use App\Domains\Property\Models\Property;
use App\Domains\Property\Models\PropertyCategory;
use App\Domains\Property\Models\PropertyFeature;
use App\Domains\Property\Models\PropertyMedia;
use App\Domains\Property\Models\PropertyType;
use App\Domains\PropertyManagement\Models\CleaningTask;
use App\Domains\PropertyManagement\Models\Community;
use App\Domains\PropertyManagement\Models\FacilityBooking;
use App\Domains\PropertyManagement\Models\MaintenanceTicket;
use App\Domains\PropertyManagement\Models\SharedFacility;
use App\Domains\Rental\Models\Lease;
use App\Domains\Rental\Models\RentalApplication;
use App\Domains\Rental\Models\RentSchedule;
use App\Domains\Rental\Models\TenantProfile;
use App\Domains\Review\Models\Review;
use App\Domains\Shared\Http\Controllers\DomainController;
use App\Domains\Shared\Services\DomainService;
use App\Domains\Stay\Models\Accommodation;
use App\Domains\Stay\Models\AccommodationAvailability;
use App\Domains\Stay\Models\AccommodationPricingRule;
use App\Domains\Stay\Models\Reservation;
use App\Domains\Subscription\Models\Subscription;
use App\Domains\Subscription\Models\SubscriptionPlan;
use App\Domains\Transaction\Models\Contract;
use App\Domains\Transaction\Models\ContractSignatory;
use App\Domains\Transaction\Models\DocumentTemplate;
use App\Domains\Transaction\Models\Invoice;
use App\Domains\Transaction\Models\LedgerAccount;
use App\Domains\Transaction\Models\LedgerEntry;
use App\Domains\Transaction\Models\LedgerEntryLine;
use App\Domains\Transaction\Models\Payment;
use App\Domains\Transaction\Models\WebhookEvent;
use App\Domains\Trust\Models\Dispute;
use App\Domains\Trust\Models\DisputeEvidence;
use App\Domains\Trust\Models\UserFlag;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    /**
     * Register domain services.
     */
    public function register(): void
    {
        $this->registerLegacyForwarders();
        $this->registerDomainBindings();
    }

    /**
     * Boot the service provider.
     */
    public function boot(): void
    {
        $this->registerDomainRoutes();
        $this->registerDomainPolicies();
    }

    /**
     * Register class aliases for legacy backward compatibility.
     */
    protected function registerLegacyForwarders(): void
    {
        $forwarders = [
            // Identity
            User::class => 'App\Models\User',
            Role::class => 'App\Models\Role',
            Permission::class => 'App\Models\Permission',
            ApiToken::class => 'App\Models\ApiToken',

            // Organization
            Organization::class => 'App\Models\Organization',
            OrganizationMember::class => 'App\Models\OrganizationMember',
            Branch::class => 'App\Models\Branch',
            Team::class => 'App\Models\Team',
            Company::class => 'App\Models\Company',
            Agent::class => 'App\Models\Agent',
            Host::class => 'App\Models\Host',
            Developer::class => 'App\Models\Developer',
            Professional::class => 'App\Models\Professional',

            // Location
            Country::class => 'App\Models\Country',
            Region::class => 'App\Models\Region',
            Zone::class => 'App\Models\Zone',
            City::class => 'App\Models\City',
            SubCity::class => 'App\Models\SubCity',
            Woreda::class => 'App\Models\Woreda',
            Kebele::class => 'App\Models\Kebele',
            Neighborhood::class => 'App\Models\Neighborhood',
            Location::class => 'App\Models\Location',
            LocationTranslation::class => 'App\Models\LocationTranslation',
            LocationGeometry::class => 'App\Models\LocationGeometry',

            // GIS
            PropertySpatialData::class => 'App\Models\PropertySpatialData',
            MapFeature::class => 'App\Models\MapFeature',

            // Property
            Property::class => 'App\Models\Property',
            PropertyCategory::class => 'App\Models\PropertyCategory',
            PropertyType::class => 'App\Models\PropertyType',
            PropertyFeature::class => 'App\Models\PropertyFeature',
            PropertyMedia::class => 'App\Models\PropertyMedia',
            Amenity::class => 'App\Models\Amenity',

            // Listing
            Listing::class => 'App\Models\Listing',
            ListingFlag::class => 'App\Models\ListingFlag',
            ListingModeration::class => 'App\Models\ListingModeration',
            ListingSearchIndex::class => 'App\Models\ListingSearchIndex',
            Favorite::class => 'App\Models\Favorite',
            Collection::class => 'App\Models\Collection',
            CollectionProperty::class => 'App\Models\CollectionProperty',

            // Engagement
            Inquiry::class => 'App\Models\Inquiry',
            Conversation::class => 'App\Models\Conversation',
            ConversationParticipant::class => 'App\Models\ConversationParticipant',
            Message::class => 'App\Models\Message',
            MessageAttachment::class => 'App\Models\MessageAttachment',
            Viewing::class => 'App\Models\Viewing',
            Offer::class => 'App\Models\Offer',
            OfferEvent::class => 'App\Models\OfferEvent',

            // Stay
            Accommodation::class => 'App\Models\Accommodation',
            AccommodationAvailability::class => 'App\Models\AccommodationAvailability',
            AccommodationPricingRule::class => 'App\Models\AccommodationPricingRule',
            Reservation::class => 'App\Models\Reservation',

            // Booking
            AccommodationBooking::class => 'App\Models\AccommodationBooking',

            // Review
            Review::class => 'App\Models\Review',

            // Development
            DevelopmentProject::class => 'App\Models\DevelopmentProject',
            DevelopmentBuilding::class => 'App\Models\DevelopmentBuilding',
            DevelopmentUnit::class => 'App\Models\DevelopmentUnit',
            PaymentPlan::class => 'App\Models\PaymentPlan',
            PaymentPlanInstallment::class => 'App\Models\PaymentPlanInstallment',

            // CRM
            Lead::class => 'App\Models\Lead',
            CrmActivity::class => 'App\Models\CrmActivity',

            // Rental
            TenantProfile::class => 'App\Models\TenantProfile',
            RentalApplication::class => 'App\Models\RentalApplication',
            Lease::class => 'App\Models\Lease',
            RentSchedule::class => 'App\Models\RentSchedule',

            // PropertyManagement
            MaintenanceTicket::class => 'App\Models\MaintenanceTicket',
            CleaningTask::class => 'App\Models\CleaningTask',
            Community::class => 'App\Models\Community',
            SharedFacility::class => 'App\Models\SharedFacility',
            FacilityBooking::class => 'App\Models\FacilityBooking',

            // Transaction
            Payment::class => 'App\Models\Payment',
            Invoice::class => 'App\Models\Invoice',
            LedgerAccount::class => 'App\Models\LedgerAccount',
            LedgerEntry::class => 'App\Models\LedgerEntry',
            LedgerEntryLine::class => 'App\Models\LedgerEntryLine',
            WebhookEvent::class => 'App\Models\WebhookEvent',
            DocumentTemplate::class => 'App\Models\DocumentTemplate',
            Contract::class => 'App\Models\Contract',
            ContractSignatory::class => 'App\Models\ContractSignatory',

            // Trust
            Dispute::class => 'App\Models\Dispute',
            DisputeEvidence::class => 'App\Models\DisputeEvidence',
            UserFlag::class => 'App\Models\UserFlag',

            // Notification
            NotificationPreference::class => 'App\Models\NotificationPreference',

            // Subscription
            SubscriptionPlan::class => 'App\Models\SubscriptionPlan',
            Subscription::class => 'App\Models\Subscription',

            // Advertising
            MarketingCampaign::class => 'App\Models\MarketingCampaign',

            // Analytics
            GeneratedReport::class => 'App\Models\GeneratedReport',
            ReportTemplate::class => 'App\Models\ReportTemplate',
            PropertyValuation::class => 'App\Models\PropertyValuation',

            // Administration
            AuditLog::class => 'App\Models\AuditLog',
        ];

        foreach ($forwarders as $target => $alias) {
            if (! class_exists($alias, false)) {
                class_alias($target, $alias);
            }
        }
    }

    /**
     * Register domain service bindings.
     */
    protected function registerDomainBindings(): void
    {
        $this->app->bind('domain.controller', function () {
            return new DomainController;
        });

        $this->app->bind('domain.service', function () {
            return new DomainService;
        });
    }

    /**
     * Register domain routes.
     */
    protected function registerDomainRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api/v1')
            ->group(function () {
                foreach ($this->getDomainDirectories() as $domain) {
                    $routeFile = base_path("app/Domains/{$domain}/routes/api.php");
                    if (file_exists($routeFile)) {
                        require $routeFile;
                    }
                }
            });

        Route::middleware(['web', 'auth'])
            ->group(function () {
                foreach ($this->getDomainDirectories() as $domain) {
                    $routeFile = base_path("app/Domains/{$domain}/routes/web.php");
                    if (file_exists($routeFile)) {
                        require $routeFile;
                    }
                }
            });
    }

    /**
     * Register domain policies.
     */
    protected function registerDomainPolicies(): void {}

    /**
     * Get all domain directories.
     */
    protected function getDomainDirectories(): array
    {
        $domainsPath = base_path('app/Domains');
        if (! is_dir($domainsPath)) {
            return [];
        }

        return array_values(array_filter(scandir($domainsPath), function ($item) use ($domainsPath) {
            return ! in_array($item, ['.', '..', 'Shared']) && is_dir($domainsPath.'/'.$item);
        }));
    }
}
