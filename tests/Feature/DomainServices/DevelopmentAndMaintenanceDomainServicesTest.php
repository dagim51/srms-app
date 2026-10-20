<?php

namespace Tests\Feature\DomainServices;

use App\Events\Development\DevelopmentUnitReserved;
use App\Events\Development\ProjectMilestoneCompleted;
use App\Events\Operations\TicketCreated;
use App\Events\Operations\TicketDispatched;
use App\Events\Operations\TicketResolved;
use App\Models\DevelopmentBuilding;
use App\Models\DevelopmentProject;
use App\Models\DevelopmentUnit;
use App\Models\MaintenanceTicket;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\PaymentPlan;
use App\Models\Property;
use App\Models\User;
use App\Services\Development\DeveloperSalesService;
use App\Services\Operations\MaintenanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DevelopmentAndMaintenanceDomainServicesTest extends TestCase
{
    use RefreshDatabase;

    protected DeveloperSalesService $developerSalesService;
    protected MaintenanceService $maintenanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->developerSalesService = app(DeveloperSalesService::class);
        $this->maintenanceService = app(MaintenanceService::class);
    }

    public function test_developer_sales_service_lifecycle_and_events(): void
    {
        Event::fake([
            ProjectMilestoneCompleted::class,
            DevelopmentUnitReserved::class,
        ]);

        $devUser = User::factory()->create();
        $buyer = User::factory()->create();

        $org = Organization::create([
            'name' => 'Addis Highrise Developers',
            'slug' => 'addis-highrise',
            'status' => 'active',
            'created_by' => $devUser->id,
        ]);

        OrganizationMember::create([
            'organization_id' => $org->id,
            'user_id' => $devUser->id,
            'status' => 'active',
            'created_by' => $devUser->id,
        ]);

        // 1. Create project
        $project = $this->developerSalesService->createProject($devUser->id, [
            'organization_id' => $org->id,
            'name' => 'Bole Horizon Tower',
            'status' => 'planning',
            'handover_date' => '2028-06-01',
        ]);
        $this->assertInstanceOf(DevelopmentProject::class, $project);

        // 2. Create building
        $building = $this->developerSalesService->createBuilding($project, $devUser->id, [
            'name' => 'Block A',
            'total_floors' => 18,
            'status' => 'planning',
        ]);
        $this->assertInstanceOf(DevelopmentBuilding::class, $building);

        // 3. Create unit
        $unit = $this->developerSalesService->createUnit($building, $devUser->id, [
            'floor_name' => 'Floor 10',
            'unit_number' => 'U-1002',
            'status' => 'available',
            'price' => 9500000,
        ]);
        $this->assertInstanceOf(DevelopmentUnit::class, $unit);

        // 4. Create payment plan
        $plan = $this->developerSalesService->createPaymentPlan($project, $devUser->id, [
            'name' => '4-Stage Milestone Plan',
        ]);
        $this->assertInstanceOf(PaymentPlan::class, $plan);

        // 5. Update milestone progress
        $updatedProject = $this->developerSalesService->updateProgress($project, $devUser->id, 40, 'Foundation Complete');
        $this->assertEquals(40, $updatedProject->progress_percentage);
        $this->assertEquals('construction', $updatedProject->status);

        Event::assertDispatched(ProjectMilestoneCompleted::class, function ($e) use ($project) {
            return $e->project->id === $project->id && $e->progressPercentage === 40 && $e->milestone === 'Foundation Complete';
        });

        // 6. Reserve unit
        $reservedUnit = $this->developerSalesService->reserveUnit($unit, $buyer->id, 9200000);
        $this->assertEquals('reserved', $reservedUnit->status);
        $this->assertEquals(9200000, $reservedUnit->price);

        Event::assertDispatched(DevelopmentUnitReserved::class, function ($e) use ($unit, $buyer) {
            return $e->unit->id === $unit->id && $e->buyerId === $buyer->id && $e->agreedPrice == 9200000;
        });
    }

    public function test_maintenance_service_lifecycle_and_events(): void
    {
        Event::fake([
            TicketCreated::class,
            TicketDispatched::class,
            TicketResolved::class,
        ]);

        $tenant = User::factory()->create();
        $technician = User::factory()->create();

        $property = Property::create([
            'property_code' => 'PROP-MAINT-01',
            'title' => 'Maintenance Test Complex',
            'status' => 'active',
        ]);

        // 1. Create ticket
        $ticket = $this->maintenanceService->createTicket($tenant->id, [
            'property_id' => $property->id,
            'title' => 'Elevator Sensor Malfunction',
            'description' => 'Elevator B sensor not detecting door obstruction.',
            'priority' => 'urgent',
        ]);
        $this->assertInstanceOf(MaintenanceTicket::class, $ticket);
        $this->assertEquals('open', $ticket->status);

        Event::assertDispatched(TicketCreated::class, function ($e) use ($ticket) {
            return $e->ticket->id === $ticket->id;
        });

        // 2. Dispatch / assign technician
        $dispatchedTicket = $this->maintenanceService->assignTicket($ticket, $technician->id);
        $this->assertEquals('assigned', $dispatchedTicket->status);
        $this->assertEquals($technician->id, $dispatchedTicket->assigned_to);

        Event::assertDispatched(TicketDispatched::class, function ($e) use ($ticket, $technician) {
            return $e->ticket->id === $ticket->id && $e->assigneeId === $technician->id;
        });

        // 3. Resolve ticket
        $resolvedTicket = $this->maintenanceService->updateTicketStatus($ticket, 'completed');
        $this->assertEquals('completed', $resolvedTicket->status);

        Event::assertDispatched(TicketResolved::class, function ($e) use ($ticket) {
            return $e->ticket->id === $ticket->id;
        });
    }
}
