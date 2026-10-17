@props([
    'title' => null,
    'description' => null,
    'icon' => null,
    'badge' => null,
    'actions' => null,
])

@php
    $currentRoute = request()->route() ? request()->route()->getName() : '';
    $currentPath = trim(request()->path(), '/');

    // Comprehensive Route & Path Metadata Catalog for all SRMS Pages
    $catalog = [
        // Dashboard Core & Overview
        'dashboard' => [
            'name' => 'Dashboard',
            'desc' => 'Monitor key performance indicators, track recent activity, and review important system statistics.',
            'icon' => 'chart',
            'badge' => 'Analytics',
        ],

        // Properties & Inventory
        'dashboard.properties.index' => [
            'name' => 'Property Inventory',
            'desc' => 'Manage registered real estate inventory, cadastral boundaries, architectural media galleries, and listing controls.',
            'icon' => 'building',
            'badge' => 'Inventory',
        ],
        'dashboard/properties' => [
            'name' => 'Property Inventory',
            'desc' => 'Manage registered real estate inventory, cadastral boundaries, architectural media galleries, and listing controls.',
            'icon' => 'building',
            'badge' => 'Inventory',
        ],
        'dashboard.properties.map' => [
            'name' => 'GIS Map Explorer',
            'desc' => 'Interactive spatial intelligence, live radius search, and geographic property distribution across Ethiopian urban centers.',
            'icon' => 'map',
            'badge' => 'GIS Spatial',
        ],
        'dashboard/properties/map' => [
            'name' => 'GIS Map Explorer',
            'desc' => 'Interactive spatial intelligence, live radius search, and geographic property distribution across Ethiopian urban centers.',
            'icon' => 'map',
            'badge' => 'GIS Spatial',
        ],
        'dashboard.properties.create' => [
            'name' => 'Register New Property',
            'desc' => 'Register new real estate property with PostGIS coordinates, category specifications, amenities, and title verification documents.',
            'icon' => 'building',
            'badge' => 'Registration',
        ],
        'dashboard/properties/create' => [
            'name' => 'Register New Property',
            'desc' => 'Register new real estate property with PostGIS coordinates, category specifications, amenities, and title verification documents.',
            'icon' => 'building',
            'badge' => 'Registration',
        ],
        'dashboard.properties.media.demo' => [
            'name' => 'Property Media Manager',
            'desc' => 'Upload architectural photography, virtual tour media, floorplans, and property showcase galleries.',
            'icon' => 'building',
            'badge' => 'Media',
        ],

        // Listings
        'dashboard.listings.index' => [
            'name' => 'Listings Management',
            'desc' => 'Manage public market listings, sales pricing, rental rates, promotion tiers, and verification lifecycle states.',
            'icon' => 'tag',
            'badge' => 'Listings',
        ],
        'dashboard/listings' => [
            'name' => 'Listings Management',
            'desc' => 'Manage public market listings, sales pricing, rental rates, promotion tiers, and verification lifecycle states.',
            'icon' => 'tag',
            'badge' => 'Listings',
        ],
        'dashboard.listings.create' => [
            'name' => 'Publish New Listing',
            'desc' => 'Publish inventory properties to the marketplace with purpose types, pricing tiers, and inspection dates.',
            'icon' => 'tag',
            'badge' => 'Publisher',
        ],
        'dashboard/listings/create' => [
            'name' => 'Publish New Listing',
            'desc' => 'Publish inventory properties to the marketplace with purpose types, pricing tiers, and inspection dates.',
            'icon' => 'tag',
            'badge' => 'Publisher',
        ],

        // Moderation & Verification
        'dashboard.moderation.*' => [
            'name' => 'Listing Moderation Queue',
            'desc' => 'Review and verify property listings submitted for public discovery, legal compliance, and quality assurance.',
            'icon' => 'shield',
            'badge' => 'Trust & Safety',
        ],
        'dashboard/moderation' => [
            'name' => 'Listing Moderation Queue',
            'desc' => 'Review and verify property listings submitted for public discovery, legal compliance, and quality assurance.',
            'icon' => 'shield',
            'badge' => 'Trust & Safety',
        ],

        // Favorites & Collections
        'dashboard.favorites.*' => [
            'name' => 'Saved Properties',
            'desc' => 'Your personal catalog of bookmarked properties, custom watchlists, and price fluctuation tracking.',
            'icon' => 'heart',
            'badge' => 'Favorites',
        ],
        'dashboard/favorites' => [
            'name' => 'Saved Properties',
            'desc' => 'Your personal catalog of bookmarked properties, custom watchlists, and price fluctuation tracking.',
            'icon' => 'heart',
            'badge' => 'Favorites',
        ],
        'dashboard.collections.*' => [
            'name' => 'Curated Collections',
            'desc' => 'Organize, group, and collaborate on shared property portfolios for private investment groups and clients.',
            'icon' => 'folder',
            'badge' => 'Portfolios',
        ],
        'dashboard/collections' => [
            'name' => 'Curated Collections',
            'desc' => 'Organize, group, and collaborate on shared property portfolios for private investment groups and clients.',
            'icon' => 'folder',
            'badge' => 'Portfolios',
        ],

        // Communications & Appointments
        'dashboard.messages.*' => [
            'name' => 'Inquiries & Messaging',
            'desc' => 'Direct communication channel between verified buyers, agents, owners, and developers with real-time audit logs.',
            'icon' => 'chat',
            'badge' => 'Communications',
        ],
        'dashboard/messages' => [
            'name' => 'Inquiries & Messaging',
            'desc' => 'Direct communication channel between verified buyers, agents, owners, and developers with real-time audit logs.',
            'icon' => 'chat',
            'badge' => 'Communications',
        ],
        'dashboard.viewings.*' => [
            'name' => 'Property Viewings',
            'desc' => 'Coordinate and manage scheduled on-site appointments, virtual tours, and viewing feedback.',
            'icon' => 'calendar',
            'badge' => 'Appointments',
        ],
        'dashboard/viewings' => [
            'name' => 'Property Viewings',
            'desc' => 'Coordinate and manage scheduled on-site appointments, virtual tours, and viewing feedback.',
            'icon' => 'calendar',
            'badge' => 'Appointments',
        ],

        // Offers & Negotiations
        'dashboard.offers.*' => [
            'name' => 'Offers & Negotiations',
            'desc' => 'Track formal purchase bids, lease offers, counter-negotiations, and contractual deposit milestones.',
            'icon' => 'document',
            'badge' => 'Negotiations',
        ],
        'dashboard/offers' => [
            'name' => 'Offers & Negotiations',
            'desc' => 'Track formal purchase bids, lease offers, counter-negotiations, and contractual deposit milestones.',
            'icon' => 'document',
            'badge' => 'Negotiations',
        ],

        // Reservations & Accommodations
        'dashboard.reservations.*' => [
            'name' => 'Marketplace Reservations',
            'desc' => 'Oversee short-stay accommodation bookings, check-in schedules, and guest reservation statuses.',
            'icon' => 'key',
            'badge' => 'Reservations',
        ],
        'dashboard/reservations' => [
            'name' => 'Marketplace Reservations',
            'desc' => 'Oversee short-stay accommodation bookings, check-in schedules, and guest reservation statuses.',
            'icon' => 'key',
            'badge' => 'Reservations',
        ],
        'dashboard.accommodations.*' => [
            'name' => 'Short-Stay Accommodations',
            'desc' => 'Manage serviced apartments, guest rooms, night rates, and hospitality occupancy settings.',
            'icon' => 'key',
            'badge' => 'Accommodations',
        ],
        'dashboard/accommodations' => [
            'name' => 'Short-Stay Accommodations',
            'desc' => 'Manage serviced apartments, guest rooms, night rates, and hospitality occupancy settings.',
            'icon' => 'key',
            'badge' => 'Accommodations',
        ],

        // Rental & Leases
        'dashboard.rental.applications' => [
            'name' => 'Rental Applications',
            'desc' => 'Process applicant background verifications, tenant income checks, and tenancy approvals.',
            'icon' => 'document',
            'badge' => 'Applications',
        ],
        'dashboard.rental.tenants' => [
            'name' => 'Tenants Directory',
            'desc' => 'Manage tenant contact profiles, active lease assignments, and payment histories.',
            'icon' => 'users',
            'badge' => 'Tenants',
        ],
        'dashboard.rental.leases' => [
            'name' => 'Lease Agreements',
            'desc' => 'Draft and monitor legal lease contracts, security deposits, and renewal terms.',
            'icon' => 'document',
            'badge' => 'Leases',
        ],
        'dashboard.rental.payments' => [
            'name' => 'Rent Payments & Receipts',
            'desc' => 'Track rent collections, overdue alerts, payment receipts, and automated billing schedules.',
            'icon' => 'cash',
            'badge' => 'Payments',
        ],
        'dashboard.rental.*' => [
            'name' => 'Rental & Lease Management',
            'desc' => 'Oversee active tenant agreements, lease terms, payment schedules, and rental applications.',
            'icon' => 'document',
            'badge' => 'Leases',
        ],
        'dashboard/rental' => [
            'name' => 'Rental & Lease Management',
            'desc' => 'Oversee active tenant agreements, lease terms, payment schedules, and rental applications.',
            'icon' => 'document',
            'badge' => 'Leases',
        ],

        // CRM & Pipelines
        'dashboard.crm.leads' => [
            'name' => 'Lead Pipeline Management',
            'desc' => 'Capture, score, and nurture inbound prospective buyers and tenant inquiries.',
            'icon' => 'chart',
            'badge' => 'Leads',
        ],
        'dashboard.crm.pipeline' => [
            'name' => 'Visual Deal Pipeline',
            'desc' => 'Kanban-style sales and lease negotiation deal stages from initial contact to closing.',
            'icon' => 'chart',
            'badge' => 'Pipeline',
        ],
        'dashboard.crm.clients' => [
            'name' => 'Client Relationship Directory',
            'desc' => 'Detailed customer profiles, interaction histories, and transaction portfolios.',
            'icon' => 'users',
            'badge' => 'Clients',
        ],
        'dashboard.crm.tasks' => [
            'name' => 'CRM Follow-Up Tasks',
            'desc' => 'Schedule call reminders, client site visits, contract signings, and task reminders.',
            'icon' => 'calendar',
            'badge' => 'Tasks',
        ],
        'dashboard.crm.commissions' => [
            'name' => 'Agent Commission Accounting',
            'desc' => 'Calculate sales splits, broker commission milestones, and disbursement records.',
            'icon' => 'cash',
            'badge' => 'Commissions',
        ],
        'dashboard.crm.*' => [
            'name' => 'Real Estate CRM & Pipeline',
            'desc' => 'Capture prospective leads, guide client negotiations through pipeline stages, and track agent commissions.',
            'icon' => 'chart',
            'badge' => 'CRM Pipeline',
        ],
        'dashboard/crm' => [
            'name' => 'Real Estate CRM & Pipeline',
            'desc' => 'Capture prospective leads, guide client negotiations through pipeline stages, and track agent commissions.',
            'icon' => 'chart',
            'badge' => 'CRM Pipeline',
        ],

        // Developer Projects
        'dashboard.projects.*' => [
            'name' => 'Developer Master Projects',
            'desc' => 'Oversee residential towers, master-planned developments, construction phases, and unit inventory.',
            'icon' => 'building',
            'badge' => 'Projects',
        ],
        'dashboard/projects' => [
            'name' => 'Developer Master Projects',
            'desc' => 'Oversee residential towers, master-planned developments, construction phases, and unit inventory.',
            'icon' => 'building',
            'badge' => 'Projects',
        ],
        'dashboard.sales.units' => [
            'name' => 'Project Units & Sales Inventory',
            'desc' => 'Monitor unit reservations, payment plan milestones, sales contracts, and buyer handover schedules.',
            'icon' => 'building',
            'badge' => 'Sales Inventory',
        ],
        'dashboard/sales' => [
            'name' => 'Project Units & Sales Inventory',
            'desc' => 'Monitor unit reservations, payment plan milestones, sales contracts, and buyer handover schedules.',
            'icon' => 'building',
            'badge' => 'Sales Inventory',
        ],

        // Operations
        'dashboard.operations.tickets' => [
            'name' => 'Maintenance Tickets',
            'desc' => 'Log, assign, and track property repairs, plumbing, electrical, and facility service requests.',
            'icon' => 'ticket',
            'badge' => 'Tickets',
        ],
        'dashboard.operations.cleaning' => [
            'name' => 'Housekeeping & Turnover',
            'desc' => 'Manage unit turnover inspections, cleaning rosters, and ready-for-occupancy checks.',
            'icon' => 'ticket',
            'badge' => 'Cleaning',
        ],
        'dashboard.operations.staff' => [
            'name' => 'Facility Staff & Contractors',
            'desc' => 'Coordinate on-site maintenance technicians, security personnel, and trusted contractors.',
            'icon' => 'users',
            'badge' => 'Staff',
        ],
        'dashboard.operations.*' => [
            'name' => 'Operations & Facilities',
            'desc' => 'Coordinate property maintenance requests, cleaning schedules, service staff assignments, and work orders.',
            'icon' => 'ticket',
            'badge' => 'Operations',
        ],
        'dashboard/operations' => [
            'name' => 'Operations & Facilities',
            'desc' => 'Coordinate property maintenance requests, cleaning schedules, service staff assignments, and work orders.',
            'icon' => 'ticket',
            'badge' => 'Operations',
        ],

        // Finance
        'dashboard.finance.transactions' => [
            'name' => 'Financial Transactions',
            'desc' => 'Monitor verified incoming payments, tenant transfers, escrow releases, and platform fees.',
            'icon' => 'cash',
            'badge' => 'Transactions',
        ],
        'dashboard.finance.invoices' => [
            'name' => 'Billing & Invoices',
            'desc' => 'Issue official tax invoices, track payment status, and export tax summaries.',
            'icon' => 'document',
            'badge' => 'Invoices',
        ],
        'dashboard.finance.ledger' => [
            'name' => 'General Financial Ledger',
            'desc' => 'Double-entry accounting ledger entries, accounts payable, receivable, and bank reconciliations.',
            'icon' => 'cash',
            'badge' => 'Ledger',
        ],
        'dashboard.finance.*' => [
            'name' => 'Financial Accounting & Ledgers',
            'desc' => 'Review transaction histories, issue invoices, monitor escrow payments, and audit general ledgers.',
            'icon' => 'cash',
            'badge' => 'Finance',
        ],
        'dashboard/finance' => [
            'name' => 'Financial Accounting & Ledgers',
            'desc' => 'Review transaction histories, issue invoices, monitor escrow payments, and audit general ledgers.',
            'icon' => 'cash',
            'badge' => 'Finance',
        ],

        // Documents
        'dashboard.documents.verifications' => [
            'name' => 'Title & Document Verifications',
            'desc' => 'Review legal verification certificates, title deed authenticity, and municipal stamp records.',
            'icon' => 'document',
            'badge' => 'Verifications',
        ],
        'dashboard.documents.*' => [
            'name' => 'Document Repository & Deeds',
            'desc' => 'Secure digital archive for title deeds, lease contracts, legal agreements, and compliance documentation.',
            'icon' => 'document',
            'badge' => 'Documents',
        ],
        'dashboard/documents' => [
            'name' => 'Document Repository & Deeds',
            'desc' => 'Secure digital archive for title deeds, lease contracts, legal agreements, and compliance documentation.',
            'icon' => 'document',
            'badge' => 'Documents',
        ],

        // Analytics
        'dashboard.analytics.reports' => [
            'name' => 'Market Performance Reports',
            'desc' => 'Generate and review deep real estate analytics, transaction volume metrics, and property yield reports.',
            'icon' => 'chart',
            'badge' => 'Reports',
        ],
        'dashboard.analytics.exports' => [
            'name' => 'Data Exports & Raw Telemetry',
            'desc' => 'Export CSV and Excel datasets for external accounting, audits, and business intelligence.',
            'icon' => 'chart',
            'badge' => 'Exports',
        ],
        'dashboard.analytics.*' => [
            'name' => 'Analytics & Performance Reports',
            'desc' => 'In-depth market intelligence, transaction volume reports, exportable metrics, and performance charts.',
            'icon' => 'chart',
            'badge' => 'Analytics',
        ],
        'dashboard/analytics' => [
            'name' => 'Analytics & Performance Reports',
            'desc' => 'In-depth market intelligence, transaction volume reports, exportable metrics, and performance charts.',
            'icon' => 'chart',
            'badge' => 'Analytics',
        ],

        // Organization
        'dashboard.organization.*' => [
            'name' => 'Organization & Tenancy Management',
            'desc' => 'Configure multi-branch administration, employee teams, role-based authorization, and corporate profile.',
            'icon' => 'building',
            'badge' => 'Organization',
        ],
        'dashboard/organization' => [
            'name' => 'Organization & Tenancy Management',
            'desc' => 'Configure multi-branch administration, employee teams, role-based authorization, and corporate profile.',
            'icon' => 'building',
            'badge' => 'Organization',
        ],

        // Admin & Users
        'dashboard.admin.users' => [
            'name' => 'User & Role Administration',
            'desc' => 'Manage system users, assign role-based permissions, monitor security audit logs, and configure platform settings.',
            'icon' => 'users',
            'badge' => 'Users',
        ],
        'dashboard.users' => [
            'name' => 'User & Role Administration',
            'desc' => 'Manage system users, assign role-based permissions, monitor security audit logs, and configure platform settings.',
            'icon' => 'users',
            'badge' => 'Users',
        ],
        'dashboard.admin.*' => [
            'name' => 'System Administration Console',
            'desc' => 'Manage system users, assign role-based permissions, monitor security audit logs, and configure platform settings.',
            'icon' => 'users',
            'badge' => 'Administration',
        ],
        'dashboard/admin' => [
            'name' => 'System Administration Console',
            'desc' => 'Manage system users, assign role-based permissions, monitor security audit logs, and configure platform settings.',
            'icon' => 'users',
            'badge' => 'Administration',
        ],

        // Settings & Profile
        'dashboard.settings.*' => [
            'name' => 'System Settings & Preferences',
            'desc' => 'Configure account credentials, notification channels, security policies, and system preferences.',
            'icon' => 'cog',
            'badge' => 'Settings',
        ],
        'dashboard.settings' => [
            'name' => 'System Settings & Preferences',
            'desc' => 'Configure account credentials, notification channels, security policies, and system preferences.',
            'icon' => 'cog',
            'badge' => 'Settings',
        ],
        'dashboard/settings' => [
            'name' => 'System Settings & Preferences',
            'desc' => 'Configure account credentials, notification channels, security policies, and system preferences.',
            'icon' => 'cog',
            'badge' => 'Settings',
        ],

        // Trust & Safety
        'dashboard.trust.*' => [
            'name' => 'Trust & Safety Center',
            'desc' => 'Investigate reported listings, review dispute cases, and enforce marketplace trust and safety standards.',
            'icon' => 'shield',
            'badge' => 'Trust & Safety',
        ],
        'dashboard/trust' => [
            'name' => 'Trust & Safety Center',
            'desc' => 'Investigate reported listings, review dispute cases, and enforce marketplace trust and safety standards.',
            'icon' => 'shield',
            'badge' => 'Trust & Safety',
        ],

        // Integrations
        'dashboard.integrations.*' => [
            'name' => 'External System Integrations',
            'desc' => 'Manage PostGIS mapping providers, payment gateway webhooks, SMS notification channels, and banking APIs.',
            'icon' => 'cube',
            'badge' => 'Integrations',
        ],
        'dashboard/integrations' => [
            'name' => 'External System Integrations',
            'desc' => 'Manage PostGIS mapping providers, payment gateway webhooks, SMS notification channels, and banking APIs.',
            'icon' => 'cube',
            'badge' => 'Integrations',
        ],

        // Alerts & Recommendations
        'dashboard.alerts.*' => [
            'name' => 'Alerts & Recommendations',
            'desc' => 'Personalized market notifications, price change alerts, and AI-recommended property matches.',
            'icon' => 'sparkles',
            'badge' => 'Alerts',
        ],
        'dashboard/alerts' => [
            'name' => 'Alerts & Recommendations',
            'desc' => 'Personalized market notifications, price change alerts, and AI-recommended property matches.',
            'icon' => 'sparkles',
            'badge' => 'Alerts',
        ],

        // AI Assistant
        'dashboard.ai.*' => [
            'name' => 'AI Real Estate Copilot',
            'desc' => 'Conversational AI assistant for natural language property queries, valuation estimates, and listing drafting.',
            'icon' => 'sparkles',
            'badge' => 'AI Copilot',
        ],
        'dashboard/ai' => [
            'name' => 'AI Real Estate Copilot',
            'desc' => 'Conversational AI assistant for natural language property queries, valuation estimates, and listing drafting.',
            'icon' => 'sparkles',
            'badge' => 'AI Copilot',
        ],
    ];

    // Find best match in catalog
    $matched = null;
    $normRoute = $currentRoute ?: '';
    $normPath = str_replace('/', '.', $currentPath);

    if ($normRoute && isset($catalog[$normRoute])) {
        $matched = $catalog[$normRoute];
    } elseif (isset($catalog[$currentPath])) {
        $matched = $catalog[$currentPath];
    } elseif (isset($catalog[$normPath])) {
        $matched = $catalog[$normPath];
    } else {
        foreach ($catalog as $key => $meta) {
            $prefix = str_ends_with($key, '.*') ? substr($key, 0, -2) : $key;
            $pathPrefix = str_replace('.', '/', $prefix);

            if ($normRoute && (str_starts_with($normRoute, $prefix) || $normRoute === $prefix)) {
                $matched = $meta;
                break;
            }
            if ($currentPath === $pathPrefix || str_starts_with($currentPath, $pathPrefix . '/') || str_starts_with($currentPath, $pathPrefix)) {
                $matched = $meta;
                break;
            }
            if ($normPath === $prefix || str_starts_with($normPath, $prefix . '.') || str_starts_with($normPath, $prefix)) {
                $matched = $meta;
                break;
            }
        }
    }

    $titleStr = $title !== null ? trim((string) $title) : '';
    $descStr = $description !== null ? trim((string) $description) : '';
    $badgeStr = $badge !== null ? trim((string) $badge) : '';

    $sectionTitle = trim((string) $__env->yieldContent('page-title'));
    $name = !empty($titleStr) ? $titleStr : ($matched['name'] ?? ($sectionTitle ?: ucwords(str_replace(['-', '_', '/'], ' ', $currentPath ?: 'Dashboard'))));
    
    // When $name is generically 'Dashboard' but we are on a specific sub-route, prefer catalog name
    if (($name === 'Dashboard' || empty($name)) && !empty($matched['name']) && $currentPath !== 'dashboard') {
        $name = $matched['name'];
    }

    $sectionDesc = trim((string) $__env->yieldContent('page-description'));
    $desc = !empty($descStr) ? $descStr : ($sectionDesc ?: ($matched['desc'] ?? 'Explore and manage records, verification data, and operational controls for this section.'));
    
    $badgeText = !empty($badgeStr) ? $badgeStr : ($matched['badge'] ?? null);
    $iconType = $icon ?: ($matched['icon'] ?? 'cube');

    $name = (string) $name;
    $desc = (string) $desc;
@endphp

<div {{ $attributes->merge(['class' => 'cinematic-fade-up page-header-card relative overflow-hidden rounded-2xl p-6 sm:p-7 bg-white/85 dark:bg-[#0D1527]/85 backdrop-blur-xl border border-slate-200/90 dark:border-slate-800/80 border-t-white/60 dark:border-t-white/10 shadow-sm transition-all mb-6']) }} data-component="page-header">

    {{-- Subtle Primary Accent Line (#C2410C) at the top --}}
    <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-[#C2410C] via-amber-500 to-transparent"></div>

    {{-- Subtle Very Light Orange Decorative Background Tint --}}
    <div class="absolute inset-0 bg-gradient-to-r from-orange-500/[0.04] via-amber-500/[0.02] to-transparent pointer-events-none" aria-hidden="true"></div>

    <div class="relative z-10 flex flex-col xl:flex-row xl:items-center justify-between gap-5">
        
        {{-- Left: Icon Container + Title & Description --}}
        <div class="flex items-start gap-4 flex-1 min-w-0">
            
            {{-- Professional Icon Container with Vibrant Two-Tone Orange Accent --}}
            <div class="w-13 h-13 rounded-2xl bg-gradient-to-tr from-[#C2410C] via-orange-500 to-amber-400 text-white shadow-md shadow-orange-500/25 ring-1 ring-orange-300/40 flex items-center justify-center flex-shrink-0 transition-transform duration-300 hover:scale-105">
                @if(in_array($iconType, ['map', 'gis']))
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                @elseif(in_array($iconType, ['building', 'properties']))
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m2.25-18v18m13.5-18v18m2.25-18v18M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12M3 3v18m9-18v18" /></svg>
                @elseif(in_array($iconType, ['search', 'discovery']))
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                @elseif(in_array($iconType, ['shield', 'moderation', 'security']))
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                @elseif(in_array($iconType, ['chart', 'analytics']))
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                @elseif(in_array($iconType, ['heart', 'favorites']))
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                @elseif(in_array($iconType, ['tag', 'listings']))
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                @elseif(in_array($iconType, ['folder', 'collections']))
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" /></svg>
                @elseif(in_array($iconType, ['chat', 'messages']))
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" /></svg>
                @elseif(in_array($iconType, ['calendar', 'viewings']))
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" /></svg>
                @elseif(in_array($iconType, ['key', 'accommodations']))
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                @elseif(in_array($iconType, ['document', 'leases']))
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                @elseif(in_array($iconType, ['users', 'staff', 'tenants', 'clients', 'team']))
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                @elseif(in_array($iconType, ['cash', 'finance', 'ledger', 'payment', 'commissions']))
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6H2.25m0 0v10.5m0-10.5h19.5m0 0v.75a.75.75 0 01-.75.75h-.75m1.5-1.5v10.5m-19.5 0h19.5m-19.5 0v.75c0 .414.336.75.75.75H4.5m15 0h.75a.75.75 0 00.75-.75v-.75m-16.5 0h16.5m-16.5 0V6.75m16.5 0V6.75m-8.25 5.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" /></svg>
                @elseif(in_array($iconType, ['ticket', 'operations', 'maintenance']))
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" /></svg>
                @elseif(in_array($iconType, ['sparkles', 'ai', 'copilot', 'assistant', 'alerts']))
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" /></svg>
                @elseif(in_array($iconType, ['cog', 'settings', 'preferences']))
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                @else
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                @endif
            </div>

            {{-- Title, Description & Badge with Primary Accent (#C2410C) --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="font-serif font-bold text-2xl sm:text-3xl text-[#C2410C] dark:text-orange-400 tracking-tight flex items-center gap-2.5">
                        {{ __($name) }}
                    </h1>
                    @if(!empty($badgeText))
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-orange-100/90 dark:bg-orange-950/70 text-[#C2410C] dark:text-orange-300 border border-orange-200 dark:border-orange-800/80 shadow-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#C2410C] dark:bg-orange-400"></span>
                            {{ __($badgeText) }}
                        </span>
                    @endif
                </div>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed font-normal">
                    {{ __($desc) }}
                </p>
            </div>
        </div>

        {{-- Right: Actions Slot --}}
        @if(isset($actions) && $actions->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2.5 xl:self-center flex-shrink-0">
                {{ $actions }}
            </div>
        @elseif(isset($slot) && $slot->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2.5 xl:self-center flex-shrink-0">
                {{ $slot }}
            </div>
        @endif

    </div>

</div>
