<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RentalWebController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tenants' => 85,
            'active_leases' => 82,
            'pending_applications' => 5,
            'overdue_rent' => '120,000 ETB'
        ];

        $recentApplications = [
            ['id' => 1, 'applicant' => 'Amanuel Tadesse', 'unit' => 'Bole Apartment 405', 'date' => '2026-10-07', 'status' => 'pending'],
            ['id' => 2, 'applicant' => 'Sara Girma', 'unit' => 'Kazanchis Villa 12', 'date' => '2026-10-06', 'status' => 'approved'],
            ['id' => 3, 'applicant' => 'Dawit Assefa', 'unit' => 'CMC Condo 201', 'date' => '2026-10-05', 'status' => 'rejected'],
        ];

        return view('frontend.rental.index', compact('stats', 'recentApplications'));
    }

    public function applications()
    {
        $applications = [
            ['id' => 1, 'applicant' => 'Amanuel Tadesse', 'unit' => 'Bole Apartment 405', 'monthly_rent' => '25,000 ETB', 'date' => '2026-10-07', 'status' => 'pending'],
            ['id' => 2, 'applicant' => 'Sara Girma', 'unit' => 'Kazanchis Villa 12', 'monthly_rent' => '65,000 ETB', 'date' => '2026-10-06', 'status' => 'approved'],
            ['id' => 3, 'applicant' => 'Dawit Assefa', 'unit' => 'CMC Condo 201', 'monthly_rent' => '15,000 ETB', 'date' => '2026-10-05', 'status' => 'rejected'],
            ['id' => 4, 'applicant' => 'Mekdes Hailu', 'unit' => 'Bole Apartment 202', 'monthly_rent' => '25,000 ETB', 'date' => '2026-10-01', 'status' => 'under_review'],
        ];

        return view('frontend.rental.applications', compact('applications'));
    }

    public function tenants()
    {
        $tenants = [
            ['id' => 1, 'name' => 'Abebe Kebede', 'unit' => 'Bole Apartment 101', 'phone' => '+251 911 234567', 'lease_end' => '2027-05-31', 'status' => 'active', 'balance' => '0 ETB'],
            ['id' => 2, 'name' => 'Sara Tadesse', 'unit' => 'CMC Condo 505', 'phone' => '+251 912 345678', 'lease_end' => '2026-11-30', 'status' => 'active', 'balance' => '25,000 ETB'],
            ['id' => 3, 'name' => 'Yonas Alemu', 'unit' => 'Kazanchis Villa 5', 'phone' => '+251 913 456789', 'lease_end' => '2027-01-15', 'status' => 'notice', 'balance' => '0 ETB'],
        ];

        return view('frontend.rental.tenants', compact('tenants'));
    }

    public function tenantShow($id)
    {
        $tenants = [
            ['id' => 1, 'name' => 'Abebe Kebede', 'unit' => 'Bole Apartment 101', 'phone' => '+251 911 234567', 'email' => 'abebe@example.com', 'lease_end' => '2027-05-31', 'status' => 'active', 'balance' => '0 ETB'],
        ];
        $tenant = collect($tenants)->firstWhere('id', (int) $id);
        if (!$tenant) $tenant = collect($tenants)->first();

        $documents = [
            ['id' => 1, 'name' => 'Kebele ID Card', 'type' => 'PDF', 'status' => 'verified', 'date' => '2026-06-01'],
            ['id' => 2, 'name' => 'Employment Letter', 'type' => 'PDF', 'status' => 'verified', 'date' => '2026-06-01'],
        ];

        $payments = [
            ['id' => 'REC-1001', 'amount' => '30,000 ETB', 'date' => '2026-10-01', 'method' => 'Bank Transfer', 'status' => 'completed'],
            ['id' => 'REC-0901', 'amount' => '30,000 ETB', 'date' => '2026-09-01', 'method' => 'Bank Transfer', 'status' => 'completed'],
            ['id' => 'REC-0801', 'amount' => '30,000 ETB', 'date' => '2026-08-01', 'method' => 'Cash', 'status' => 'completed'],
        ];

        return view('frontend.rental.tenant_show', compact('tenant', 'documents', 'payments'));
    }

    public function leases()
    {
        $leases = [
            ['id' => 'LSE-2026-045', 'tenant' => 'Abebe Kebede', 'unit' => 'Bole Apartment 101', 'start_date' => '2026-06-01', 'end_date' => '2027-05-31', 'rent' => '30,000 ETB', 'status' => 'active'],
            ['id' => 'LSE-2025-112', 'tenant' => 'Sara Tadesse', 'unit' => 'CMC Condo 505', 'start_date' => '2025-12-01', 'end_date' => '2026-11-30', 'rent' => '25,000 ETB', 'status' => 'expiring_soon'],
            ['id' => 'LSE-2026-012', 'tenant' => 'Yonas Alemu', 'unit' => 'Kazanchis Villa 5', 'start_date' => '2026-01-15', 'end_date' => '2027-01-14', 'rent' => '85,000 ETB', 'status' => 'active'],
        ];

        return view('frontend.rental.leases', compact('leases'));
    }

    public function leaseShow($id)
    {
        $lease = ['id' => $id, 'tenant' => 'Abebe Kebede', 'unit' => 'Bole Apartment 101', 'start_date' => '2026-06-01', 'end_date' => '2027-05-31', 'rent' => '30,000 ETB', 'deposit' => '60,000 ETB', 'status' => 'active', 'payment_frequency' => 'Monthly'];
        
        $schedule = [
            ['month' => 'Oct 2026', 'due_date' => '2026-10-01', 'amount' => '30,000 ETB', 'status' => 'paid', 'paid_date' => '2026-10-01'],
            ['month' => 'Nov 2026', 'due_date' => '2026-11-01', 'amount' => '30,000 ETB', 'status' => 'pending', 'paid_date' => null],
            ['month' => 'Dec 2026', 'due_date' => '2026-12-01', 'amount' => '30,000 ETB', 'status' => 'pending', 'paid_date' => null],
            ['month' => 'Jan 2027', 'due_date' => '2027-01-01', 'amount' => '30,000 ETB', 'status' => 'pending', 'paid_date' => null],
        ];

        return view('frontend.rental.lease_show', compact('lease', 'schedule'));
    }

    public function payments()
    {
        $payments = [
            ['id' => 'REC-1005', 'tenant' => 'Meron Bekele', 'unit' => 'Bole Apt 204', 'amount' => '28,000 ETB', 'date' => '2026-10-05', 'method' => 'Bank Transfer', 'status' => 'pending_verification'],
            ['id' => 'REC-1004', 'tenant' => 'Dawit Girma', 'unit' => 'CMC Condo 105', 'amount' => '15,000 ETB', 'date' => '2026-10-04', 'method' => 'Telebirr', 'status' => 'completed'],
            ['id' => 'REC-1001', 'tenant' => 'Abebe Kebede', 'unit' => 'Bole Apt 101', 'amount' => '30,000 ETB', 'date' => '2026-10-01', 'method' => 'Bank Transfer', 'status' => 'completed'],
        ];

        return view('frontend.rental.payments', compact('payments'));
    }
}
