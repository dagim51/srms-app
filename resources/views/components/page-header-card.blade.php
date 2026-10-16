@props([
    'title' => null,
    'description' => null,
    'icon' => null,
    'badge' => null,
])

@php
    $currentRoute = request()->route() ? request()->route()->getName() : '';
    $currentPath = trim(request()->path(), '/');

    // Comprehensive Route & Path Metadata Catalog for all SRMS Dashboard Modules
    $catalog = [
        // Dashboard Core & Overview
        'dashboard' => [
            'name' => 'System Dashboard',
            'desc' => 'Real-time performance indicators, active operations, verified properties, and administrative overview.',
            'icon' => 'chart',
            'badge' => 'Overview',
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
            'desc' => 'Register new real estate property with PostGIS coordinates, category types, amenities, and ownership verification documents.',
            'icon' => 'building',
            'badge' => 'Registration',
        ],
        'dashboard/properties/create' => [
            'name' => 'Register New Property',
            'desc' => 'Register new real estate property with PostGIS coordinates, category types, amenities, and ownership verification documents.',
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
            'name' => 'Moderation & Verification Queue',
            'desc' => 'Trust & Safety compliance center for evaluating submitted listings, legal title deeds, and ownership certificates.',
            'icon' => 'shield',
            'badge' => 'Trust & Safety',
        ],
        'dashboard/moderation' => [
            'name' => 'Moderation & Verification Queue',
            'desc' => 'Trust & Safety compliance center for evaluating submitted listings, legal title deeds, and ownership certificates.',
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

        // Offers & Reservations
        'dashboard.offers.*' => [
            'name' => 'Offers & Negotiations',
            'desc' => 'Formal legally binding purchase offers, rental counter-proposals, and negotiation audit trail.',
            'icon' => 'document',
            'badge' => 'Negotiations',
        ],
        'dashboard/offers' => [
            'name' => 'Offers & Negotiations',
            'desc' => 'Formal legally binding purchase offers, rental counter-proposals, and negotiation audit trail.',
            'icon' => 'document',
            'badge' => 'Negotiations',
        ],
        'dashboard.reservations.*' => [
            'name' => 'Marketplace Reservations',
            'desc' => 'Holding deposits, property reservation locks, and escrow commitment tracking.',
            'icon' => 'document',
            'badge' => 'Reservations',
        ],
        'dashboard/reservations' => [
            'name' => 'Marketplace Reservations',
            'desc' => 'Holding deposits, property reservation locks, and escrow commitment tracking.',
            'icon' => 'document',
            'badge' => 'Reservations',
        ],

        // Accommodations
        'dashboard.accommodations.*' => [
            'name' => 'Short-Stay Accommodations',
            'desc' => 'Host dashboard for furnished serviced residences, guest houses, and nightly vacation living.',
            'icon' => 'key',
            'badge' => 'Hospitality',
        ],
        'dashboard/accommodations' => [
            'name' => 'Short-Stay Accommodations',
            'desc' => 'Host dashboard for furnished serviced residences, guest houses, and nightly vacation living.',
            'icon' => 'key',
            'badge' => 'Hospitality',
        ],

        // Developer Sales
        'dashboard.sales.units' => [
            'name' => 'Developer Units & Sales',
            'desc' => 'Track off-plan unit sales, inventory pricing, floor layouts, and sales lifecycle status.',
            'icon' => 'cube',
            'badge' => 'Developer Sales',
        ],
        'dashboard.sales.buyers' => [
            'name' => 'Buyers & Investors Pipeline',
            'desc' => 'Track prospective purchasers, KYC verification stages, and unit reservation commitments.',
            'icon' => 'cube',
            'badge' => 'Buyers',
        ],
        'dashboard.sales.payment_plans' => [
            'name' => 'Milestone Payment Schedules',
            'desc' => 'Manage construction-linked milestone invoices, escrow installment terms, and payment schedules.',
            'icon' => 'chart',
            'badge' => 'Payment Plans',
        ],
        'dashboard.sales.contracts' => [
            'name' => 'Sales Contracts & Deeds',
            'desc' => 'Formalize developer sales agreements, digital notarizations, and binding property purchase contracts.',
            'icon' => 'document',
            'badge' => 'Contracts',
        ],
        'dashboard.sales.handovers' => [
            'name' => 'Unit Handover & Snagging',
            'desc' => 'Inspect unit completion checklists, keys handover certificates, and occupancy permits.',
            'icon' => 'key',
            'badge' => 'Handovers',
        ],
        'dashboard.sales.*' => [
            'name' => 'Developer Sales Management',
            'desc' => 'Track off-plan unit sales, buyer reservation pipelines, milestone payment schedules, and property handovers.',
            'icon' => 'cube',
            'badge' => 'Developer Sales',
        ],
        'dashboard/sales' => [
            'name' => 'Developer Sales Management',
            'desc' => 'Track off-plan unit sales, buyer reservation pipelines, milestone payment schedules, and property handovers.',
            'icon' => 'cube',
            'badge' => 'Developer Sales',
        ],

        // Tenancy & Leases
        'dashboard.rental.applications' => [
            'name' => 'Rental Applications',
            'desc' => 'Screen tenant applications, employment verifications, and credit assessments.',
            'icon' => 'document',
            'badge' => 'Applications',
        ],
        'dashboard.rental.tenants' => [
            'name' => 'Tenants Directory',
            'desc' => 'Active leaseholders, contact records, emergency references, and tenancy histories.',
            'icon' => 'document',
            'badge' => 'Tenants',
        ],
        'dashboard.rental.leases' => [
            'name' => 'Lease Agreements',
            'desc' => 'Commercial and residential lease terms, renewal dates, and legal clauses.',
            'icon' => 'document',
            'badge' => 'Leases',
        ],
        'dashboard.rental.payments' => [
            'name' => 'Rent Collections & Ledger',
            'desc' => 'Automated rent payment tracking, receipt generation, and overdue balance reminders.',
            'icon' => 'chart',
            'badge' => 'Collections',
        ],
        'dashboard.rental.*' => [
            'name' => 'Tenancy & Lease Management',
            'desc' => 'Commercial and residential leases, automated rent schedules, tenant profiles, and payment histories.',
            'icon' => 'document',
            'badge' => 'Tenancy',
        ],
        'dashboard/rental' => [
            'name' => 'Tenancy & Lease Management',
            'desc' => 'Commercial and residential leases, automated rent schedules, tenant profiles, and payment histories.',
            'icon' => 'document',
            'badge' => 'Tenancy',
        ],

        // Facility Operations
        'dashboard.operations.tickets' => [
            'name' => 'Maintenance Work Orders',
            'desc' => 'Submit and track facility repair tickets, urgency levels, and vendor assignments.',
            'icon' => 'shield',
            'badge' => 'Work Orders',
        ],
        'dashboard.operations.cleaning' => [
            'name' => 'Housekeeping & Turnover',
            'desc' => 'Manage housekeeping schedules, turnover inspections, and linen logistics for serviced units.',
            'icon' => 'shield',
            'badge' => 'Turnover',
        ],
        'dashboard.operations.staff' => [
            'name' => 'Operations Staff & Vendors',
            'desc' => 'Duty rosters, external service contractor details, and facility maintenance dispatch.',
            'icon' => 'shield',
            'badge' => 'Staff',
        ],
        'dashboard.operations.*' => [
            'name' => 'Facility Operations & Maintenance',
            'desc' => 'Service requests, maintenance ticketing, housekeeping logs, and contractor assignments.',
            'icon' => 'shield',
            'badge' => 'Operations',
        ],
        'dashboard/operations' => [
            'name' => 'Facility Operations & Maintenance',
            'desc' => 'Service requests, maintenance ticketing, housekeeping logs, and contractor assignments.',
            'icon' => 'shield',
            'badge' => 'Operations',
        ],

        // Financial Operations
        'dashboard.finance.transactions' => [
            'name' => 'Transaction History & Logs',
            'desc' => 'Comprehensive transaction logs, payment method breakdowns, and audit references.',
            'icon' => 'chart',
            'badge' => 'Transactions',
        ],
        'dashboard.finance.invoices' => [
            'name' => 'Billing & Tax Invoices',
            'desc' => 'Generate official commercial invoices, tax receipts, and payment vouchers.',
            'icon' => 'chart',
            'badge' => 'Invoices',
        ],
        'dashboard.finance.ledger' => [
            'name' => 'Double-Entry General Ledger',
            'desc' => 'Escrow accounts reconciliation, balance sheets, and chart of accounts.',
            'icon' => 'chart',
            'badge' => 'Ledger',
        ],
        'dashboard.finance.*' => [
            'name' => 'Financial Operations & Ledger',
            'desc' => 'Transaction audit trails, fee reconciliation, escrow disbursements, and financial accounting reports.',
            'icon' => 'chart',
            'badge' => 'Finance',
        ],
        'dashboard/finance' => [
            'name' => 'Financial Operations & Ledger',
            'desc' => 'Transaction audit trails, fee reconciliation, escrow disbursements, and financial accounting reports.',
            'icon' => 'chart',
            'badge' => 'Finance',
        ],

        // Documents & Verification
        'dashboard.documents.verifications' => [
            'name' => 'Document Verification Queue',
            'desc' => 'Audit land title certificates, digital signatures, and government registry stamps.',
            'icon' => 'document',
            'badge' => 'Verifications',
        ],
        'dashboard.documents.*' => [
            'name' => 'Document & Certificate Verification',
            'desc' => 'Digital repository for digitalized title deeds, site plans, legal contracts, and notary stamps.',
            'icon' => 'document',
            'badge' => 'Documents',
        ],
        'dashboard/documents' => [
            'name' => 'Document & Certificate Verification',
            'desc' => 'Digital repository for digitalized title deeds, site plans, legal contracts, and notary stamps.',
            'icon' => 'document',
            'badge' => 'Documents',
        ],

        // Trust, Disputes & Safety
        'dashboard.trust.reports' => [
            'name' => 'Reported Listings & Issues',
            'desc' => 'Investigate user flags, policy violations, and property listing irregularities.',
            'icon' => 'shield',
            'badge' => 'Reports',
        ],
        'dashboard.trust.disputes' => [
            'name' => 'Dispute Resolution Center',
            'desc' => 'Arbitrate deposit disagreements, escrow freezes, and contractual disputes.',
            'icon' => 'shield',
            'badge' => 'Disputes',
        ],
        'dashboard.trust.*' => [
            'name' => 'Trust, Disputes & Safety',
            'desc' => 'Escrow protection protocols, fraud prevention cases, identity verification, and dispute resolution.',
            'icon' => 'shield',
            'badge' => 'Trust & Safety',
        ],
        'dashboard/trust' => [
            'name' => 'Trust, Disputes & Safety',
            'desc' => 'Escrow protection protocols, fraud prevention cases, identity verification, and dispute resolution.',
            'icon' => 'shield',
            'badge' => 'Trust & Safety',
        ],

        // Admin Platform
        'dashboard.admin.users.create' => [
            'name' => 'Create User Account',
            'desc' => 'Provision new administrative staff, listing agents, property managers, and assigned branch credentials.',
            'icon' => 'shield',
            'badge' => 'User Provisioning',
        ],
        'dashboard/admin/users/create' => [
            'name' => 'Create User Account',
            'desc' => 'Provision new administrative staff, listing agents, property managers, and assigned branch credentials.',
            'icon' => 'shield',
            'badge' => 'User Provisioning',
        ],
        'dashboard.users.create' => [
            'name' => 'Create User Account',
            'desc' => 'Provision new administrative staff, listing agents, property managers, and assigned branch credentials.',
            'icon' => 'shield',
            'badge' => 'User Provisioning',
        ],
        'dashboard/users/create' => [
            'name' => 'Create User Account',
            'desc' => 'Provision new administrative staff, listing agents, property managers, and assigned branch credentials.',
            'icon' => 'shield',
            'badge' => 'User Provisioning',
        ],
        'dashboard.admin.users' => [
            'name' => 'User & Role Administration',
            'desc' => 'Manage platform users, assign RBAC permissions, and oversee team access levels.',
            'icon' => 'shield',
            'badge' => 'Users',
        ],
        'dashboard/admin/users' => [
            'name' => 'User & Role Administration',
            'desc' => 'Manage platform users, assign RBAC permissions, and oversee team access levels.',
            'icon' => 'shield',
            'badge' => 'Users',
        ],
        'dashboard.users' => [
            'name' => 'User & Role Administration',
            'desc' => 'Manage platform users, assign RBAC permissions, and oversee team access levels.',
            'icon' => 'shield',
            'badge' => 'Users',
        ],
        'dashboard/users' => [
            'name' => 'User & Role Administration',
            'desc' => 'Manage platform users, assign RBAC permissions, and oversee team access levels.',
            'icon' => 'shield',
            'badge' => 'Users',
        ],
        'dashboard.admin.audit' => [
            'name' => 'System Security Audit Log',
            'desc' => 'Immutable security activity trail, IP geolocation logs, and administrative actions.',
            'icon' => 'shield',
            'badge' => 'Audit Log',
        ],
        'dashboard.admin.settings' => [
            'name' => 'System Platform Settings',
            'desc' => 'Configure global fee structures, regional currencies, map keys, and feature flags.',
            'icon' => 'shield',
            'badge' => 'Settings',
        ],
        'dashboard.admin.permissions' => [
            'name' => 'Access Control & Permissions Matrix',
            'desc' => 'Define granular operational permissions and determine what roles and users are allowed to do.',
            'icon' => 'shield',
            'badge' => 'Access Control',
        ],
        'dashboard/admin/permissions' => [
            'name' => 'Access Control & Permissions Matrix',
            'desc' => 'Define granular operational permissions and determine what roles and users are allowed to do.',
            'icon' => 'shield',
            'badge' => 'Access Control',
        ],
        'dashboard.settings' => [
            'name' => 'Account & Platform Settings',
            'desc' => 'Configure profile credentials, agency identity, regional units, alert channels, and security.',
            'icon' => 'cog',
            'badge' => 'Settings',
        ],
        'dashboard/settings' => [
            'name' => 'Account & Platform Settings',
            'desc' => 'Configure profile credentials, agency identity, regional units, alert channels, and security.',
            'icon' => 'cog',
            'badge' => 'Settings',
        ],
        'dashboard.admin.*' => [
            'name' => 'Administration Platform',
            'desc' => 'User directory, role assignments, security audit logs, and system configuration settings.',
            'icon' => 'shield',
            'badge' => 'Administration',
        ],
        'dashboard/admin' => [
            'name' => 'Administration Platform',
            'desc' => 'User directory, role assignments, security audit logs, and system configuration settings.',
            'icon' => 'shield',
            'badge' => 'Administration',
        ],

        // Analytics & Reporting
        'dashboard.analytics.reports' => [
            'name' => 'Custom Market Reports',
            'desc' => 'Generate in-depth quarterly market performance and property absorption reports.',
            'icon' => 'chart',
            'badge' => 'Reports',
        ],
        'dashboard.analytics.exports' => [
            'name' => 'Data Export & Downloads',
            'desc' => 'Export clean CSV, Excel, and JSON datasets for business intelligence and external analysis.',
            'icon' => 'chart',
            'badge' => 'Exports',
        ],
        'dashboard.analytics.*' => [
            'name' => 'Market Analytics & Reporting',
            'desc' => 'Comprehensive price index trends, absorption rates, and regional real estate growth statistics.',
            'icon' => 'chart',
            'badge' => 'Analytics',
        ],
        'dashboard/analytics' => [
            'name' => 'Market Analytics & Reporting',
            'desc' => 'Comprehensive price index trends, absorption rates, and regional real estate growth statistics.',
            'icon' => 'chart',
            'badge' => 'Analytics',
        ],

        // Alerts & Recommendations
        'dashboard.alerts.*' => [
            'name' => 'Search Alerts & Saved Searches',
            'desc' => 'Manage automated email notifications, price drop alerts, and instant match preferences.',
            'icon' => 'search',
            'badge' => 'Alerts',
        ],
        'dashboard/alerts' => [
            'name' => 'Search Alerts & Saved Searches',
            'desc' => 'Manage automated email notifications, price drop alerts, and instant match preferences.',
            'icon' => 'search',
            'badge' => 'Alerts',
        ],
        'dashboard.recommendations.*' => [
            'name' => 'AI Property Recommendations',
            'desc' => 'Personalized real estate suggestions powered by search history, saved preferences, and spatial affinity.',
            'icon' => 'search',
            'badge' => 'Recommendations',
        ],
        'dashboard/recommendations' => [
            'name' => 'AI Property Recommendations',
            'desc' => 'Personalized real estate suggestions powered by search history, saved preferences, and spatial affinity.',
            'icon' => 'search',
            'badge' => 'Recommendations',
        ],

        // Advanced GIS
        'dashboard.gis.*' => [
            'name' => 'Advanced GIS Spatial Analysis',
            'desc' => 'Cadastral boundary overlays, urban planning zones, infrastructure proximity, and geospatial queries.',
            'icon' => 'map',
            'badge' => 'GIS Spatial',
        ],
        'dashboard/map' => [
            'name' => 'Advanced GIS Spatial Analysis',
            'desc' => 'Cadastral boundary overlays, urban planning zones, infrastructure proximity, and geospatial queries.',
            'icon' => 'map',
            'badge' => 'GIS Spatial',
        ],

        // Valuation & Market Intelligence
        'dashboard.intelligence.valuation' => [
            'name' => 'Automated Valuation Models (AVM)',
            'desc' => 'Algorithmic fair market estimates, historical price comps, and land valuation indices.',
            'icon' => 'chart',
            'badge' => 'Valuation',
        ],
        'dashboard.intelligence.calculators' => [
            'name' => 'Investment & Mortgage Calculators',
            'desc' => 'Model debt service coverage, cap rates, rental yield curves, and mortgage amortizations.',
            'icon' => 'chart',
            'badge' => 'Calculators',
        ],
        'dashboard.intelligence.portfolio' => [
            'name' => 'Portfolio Performance & Yield',
            'desc' => 'Track real estate asset appreciation, aggregate net rental yields, and equity growth.',
            'icon' => 'chart',
            'badge' => 'Portfolio',
        ],
        'dashboard.intelligence.*' => [
            'name' => 'Valuation & Market Intelligence',
            'desc' => 'Automated valuation models (AVM), mortgage calculators, and investment ROI forecasting.',
            'icon' => 'chart',
            'badge' => 'Intelligence',
        ],
        'dashboard/intelligence' => [
            'name' => 'Valuation & Market Intelligence',
            'desc' => 'Automated valuation models (AVM), mortgage calculators, and investment ROI forecasting.',
            'icon' => 'chart',
            'badge' => 'Intelligence',
        ],

        // Professional Services
        'dashboard.services.dashboard' => [
            'name' => 'Provider Practice Console',
            'desc' => 'Service provider dashboard for managing client cases, incoming consultation briefs, and service milestones.',
            'icon' => 'building',
            'badge' => 'Provider Console',
        ],
        'dashboard.services.*' => [
            'name' => 'Professional Services Console',
            'desc' => 'Manage professional client requests, service booking orders, surveyor reports, and practitioner profiles.',
            'icon' => 'building',
            'badge' => 'Services',
        ],
        'dashboard/services' => [
            'name' => 'Professional Services Console',
            'desc' => 'Manage professional client requests, service booking orders, surveyor reports, and practitioner profiles.',
            'icon' => 'building',
            'badge' => 'Services',
        ],

        // System Integrations
        'dashboard.integrations.history' => [
            'name' => 'Webhook & Sync History',
            'desc' => 'Real-time webhook payloads, sync failure retry logs, and integration telemetry.',
            'icon' => 'cube',
            'badge' => 'Sync History',
        ],
        'dashboard.integrations.*' => [
            'name' => 'External System Integrations',
            'desc' => 'PostGIS mapping providers, payment gateway webhooks, SMS notification channels, and banking APIs.',
            'icon' => 'cube',
            'badge' => 'Integrations',
        ],
        'dashboard/integrations' => [
            'name' => 'External System Integrations',
            'desc' => 'PostGIS mapping providers, payment gateway webhooks, SMS notification channels, and banking APIs.',
            'icon' => 'cube',
            'badge' => 'Integrations',
        ],

        // AI Search & Assistant
        'dashboard.ai.search' => [
            'name' => 'AI Intelligent Property Search',
            'desc' => 'Natural language semantic queries to discover exact property matches across Ethiopia.',
            'icon' => 'search',
            'badge' => 'AI Search',
        ],
        'dashboard.ai.assistant' => [
            'name' => 'SRMS AI Copilot Assistant',
            'desc' => 'Conversational assistant to analyze property data, draft listings, and evaluate contracts.',
            'icon' => 'search',
            'badge' => 'AI Copilot',
        ],
        'dashboard.ai.*' => [
            'name' => 'AI Search & Copilot Assistant',
            'desc' => 'Conversational AI assistant for real estate queries, listing drafting, and smart spatial queries.',
            'icon' => 'search',
            'badge' => 'AI Intelligence',
        ],
        'dashboard/ai' => [
            'name' => 'AI Search & Copilot Assistant',
            'desc' => 'Conversational AI assistant for real estate queries, listing drafting, and smart spatial queries.',
            'icon' => 'search',
            'badge' => 'AI Intelligence',
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
            'icon' => 'chart',
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
            'icon' => 'chart',
            'badge' => 'Commissions',
        ],
        'dashboard.crm.*' => [
            'name' => 'Real Estate CRM & Pipeline',
            'desc' => 'Lead pipeline tracking, buyer qualification stages, agent assignments, and commission calculations.',
            'icon' => 'chart',
            'badge' => 'CRM Pipeline',
        ],
        'dashboard/crm' => [
            'name' => 'Real Estate CRM & Pipeline',
            'desc' => 'Lead pipeline tracking, buyer qualification stages, agent assignments, and commission calculations.',
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

        // Organization Management
        'dashboard.organization.create' => [
            'name' => 'Register New Organization',
            'desc' => 'Provision a multi-tenant corporate real estate entity, developer account, or property management agency.',
            'icon' => 'building',
            'badge' => 'Registration',
        ],
        'dashboard/organization/create' => [
            'name' => 'Register New Organization',
            'desc' => 'Provision a multi-tenant corporate real estate entity, developer account, or property management agency.',
            'icon' => 'building',
            'badge' => 'Registration',
        ],
        'dashboard.organization.profile' => [
            'name' => 'Organization Profile',
            'desc' => 'Manage corporate entity details, trade licenses, TIN registration, and branding.',
            'icon' => 'building',
            'badge' => 'Profile',
        ],
        'dashboard.organization.branches' => [
            'name' => 'Branch Offices & Regions',
            'desc' => 'Coordinate multi-city branch offices across Addis Ababa and regional commercial hubs.',
            'icon' => 'building',
            'badge' => 'Branches',
        ],
        'dashboard.organization.teams' => [
            'name' => 'Departmental Teams',
            'desc' => 'Organize sales agents, property managers, legal reviewers, and accounting teams.',
            'icon' => 'building',
            'badge' => 'Teams',
        ],
        'dashboard.organization.members' => [
            'name' => 'Team Members Directory',
            'desc' => 'Manage team employee profiles, invitations, and assigned department roles.',
            'icon' => 'building',
            'badge' => 'Members',
        ],
        'dashboard.organization.*' => [
            'name' => 'Organization & Tenancy Management',
            'desc' => 'Multi-branch administration, employee teams, role-based authorization, and company profile.',
            'icon' => 'building',
            'badge' => 'Organization',
        ],
        'dashboard/organization' => [
            'name' => 'Organization & Tenancy Management',
            'desc' => 'Multi-branch administration, employee teams, role-based authorization, and company profile.',
            'icon' => 'building',
            'badge' => 'Organization',
        ],

        // User Preferences & Reviews
        'dashboard.preferences' => [
            'name' => 'User Preferences & Notifications',
            'desc' => 'Configure alert channels, marketing communications, theme settings, and security notifications.',
            'icon' => 'cog',
            'badge' => 'Preferences',
        ],
        'dashboard/preferences' => [
            'name' => 'User Preferences & Notifications',
            'desc' => 'Configure alert channels, marketing communications, theme settings, and security notifications.',
            'icon' => 'cog',
            'badge' => 'Preferences',
        ],
        'dashboard.reviews.*' => [
            'name' => 'Ratings & Reviews Management',
            'desc' => 'Moderate tenant reviews, host ratings, customer satisfaction scores, and public testimonials.',
            'icon' => 'heart',
            'badge' => 'Reviews',
        ],
        'dashboard/reviews' => [
            'name' => 'Ratings & Reviews Management',
            'desc' => 'Moderate tenant reviews, host ratings, customer satisfaction scores, and public testimonials.',
            'icon' => 'heart',
            'badge' => 'Reviews',
        ],
        'dashboard.profile' => [
            'name' => 'Account Profile',
            'desc' => 'Manage user credentials, personal verification badges, and security credentials.',
            'icon' => 'shield',
            'badge' => 'Account',
        ],
        'dashboard/profile' => [
            'name' => 'Account Profile',
            'desc' => 'Manage user credentials, personal verification badges, and security credentials.',
            'icon' => 'shield',
            'badge' => 'Account',
        ],
    ];

    // Find best match in catalog
    $matched = null;
    $normRoute = $currentRoute ?: '';
    $normPath = str_replace('/', '.', $currentPath);

    // 1. Direct key match (by route name, URL path, or dot-normalized path)
    if ($normRoute && isset($catalog[$normRoute])) {
        $matched = $catalog[$normRoute];
    } elseif (isset($catalog[$currentPath])) {
        $matched = $catalog[$currentPath];
    } elseif (isset($catalog[$normPath])) {
        $matched = $catalog[$normPath];
    } else {
        // 2. Prefix & Wildcard matching
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

    // Resolve final name and description safely handling ComponentSlot or string
    $titleStr = $title !== null ? trim((string) $title) : '';
    $descStr = $description !== null ? trim((string) $description) : '';
    $badgeStr = $badge !== null ? trim((string) $badge) : '';

    $sectionTitle = trim((string) $__env->yieldContent('page-title'));
    
    // Determine page name
    $name = !empty($titleStr) ? $titleStr : ($matched['name'] ?? ($sectionTitle ?: ucwords(str_replace(['-', '_', '/'], ' ', $currentPath ?: 'Dashboard'))));
    
    // When $name is generically 'Dashboard' but we have a more specific catalog match on a sub-route, prefer catalog name
    if (($name === 'Dashboard' || empty($name)) && !empty($matched['name']) && $currentPath !== 'dashboard') {
        $name = $matched['name'];
    }

    // Determine short description
    $sectionDesc = trim((string) $__env->yieldContent('page-description'));
    $desc = !empty($descStr) ? $descStr : ($sectionDesc ?: ($matched['desc'] ?? 'Explore and manage records, verification data, and operational controls for this section.'));
    
    $badgeText = !empty($badgeStr) ? $badgeStr : ($matched['badge'] ?? 'SRMS');
    $iconType = $icon ?: ($matched['icon'] ?? 'cube');

    // Guarantee string types for translation lookups
    $name = (string) $name;
    $desc = (string) $desc;
    $badgeText = (string) $badgeText;
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-3xl p-5 sm:p-7 bg-gradient-to-r from-[#9A3412] via-[#C2410C] to-[#7C2D12] dark:from-[#7C2D12] dark:via-[#9A3412] dark:to-[#431407] border border-orange-600/50 dark:border-orange-700/60 shadow-lg shadow-orange-950/25 text-white']) }} id="page-header-card">

    {{-- Subtle Top Brand Accent Line (Gold/Amber) --}}
    <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-amber-300 via-orange-300 to-amber-400"></div>

    {{-- Ambient Dark Orange Glow Blobs (Static) --}}
    <div class="absolute -top-16 -right-16 w-64 h-64 bg-amber-400/20 dark:bg-orange-500/15 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
    <div class="absolute -bottom-16 -left-16 w-64 h-64 bg-black/20 rounded-full blur-2xl pointer-events-none" aria-hidden="true"></div>

    <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
        
        <div class="flex items-start sm:items-center gap-5">
            {{-- Glowing Icon Container --}}
            <div class="relative flex-shrink-0 mt-0.5 sm:mt-0">
                <div class="absolute -inset-1 rounded-2xl bg-amber-400/20 blur-xs pointer-events-none"></div>

                {{-- The Icon Box --}}
                <div class="relative w-15 h-15 rounded-2xl bg-gradient-to-br from-amber-400 via-orange-500 to-amber-600 flex items-center justify-center text-white shadow-md shadow-orange-950/40 border border-amber-300/40">
                    @if(in_array($iconType, ['map', 'gis']))
                        <svg class="w-7.5 h-7.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                    @elseif(in_array($iconType, ['building', 'properties']))
                        <svg class="w-7.5 h-7.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m2.25-18v18m13.5-18v18m2.25-18v18M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12M3 3v18m9-18v18" /></svg>
                    @elseif(in_array($iconType, ['search', 'discovery']))
                        <svg class="w-7.5 h-7.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    @elseif(in_array($iconType, ['shield', 'moderation', 'security']))
                        <svg class="w-7.5 h-7.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                    @elseif(in_array($iconType, ['chart', 'analytics']))
                        <svg class="w-7.5 h-7.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                    @elseif(in_array($iconType, ['heart', 'favorites']))
                        <svg class="w-7.5 h-7.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                    @elseif(in_array($iconType, ['tag', 'listings']))
                        <svg class="w-7.5 h-7.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                    @elseif(in_array($iconType, ['folder', 'collections']))
                        <svg class="w-7.5 h-7.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" /></svg>
                    @elseif(in_array($iconType, ['chat', 'messages']))
                        <svg class="w-7.5 h-7.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" /></svg>
                    @elseif(in_array($iconType, ['lock', 'auth']))
                        <svg class="w-7.5 h-7.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                    @elseif(in_array($iconType, ['calendar', 'viewings']))
                        <svg class="w-7.5 h-7.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" /></svg>
                    @elseif(in_array($iconType, ['key', 'accommodations']))
                        <svg class="w-7.5 h-7.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                    @elseif(in_array($iconType, ['document', 'leases']))
                        <svg class="w-7.5 h-7.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    @else
                        <svg class="w-7.5 h-7.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                    @endif
                </div>

                {{-- Static status dot --}}
                <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5">
                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-amber-400 border-2 border-[#9A3412]"></span>
                </span>
            </div>

            {{-- Page Title, Description & Badge --}}
            <div class="min-w-0">
                <div class="flex items-center gap-2.5 flex-wrap mb-1.5">
                    <h1 class="font-display font-black text-xl sm:text-2xl lg:text-3xl text-white tracking-tight drop-shadow-xs">
                        {{ __($name) }}
                    </h1>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs sm:text-sm font-bold uppercase tracking-wider bg-orange-950/60 text-amber-200 border border-orange-500/40 shadow-2xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                        {{ __($badgeText) }}
                    </span>
                </div>
                <p class="text-sm sm:text-base text-orange-100/95 dark:text-orange-200/90 max-w-4xl leading-relaxed font-normal">
                    {{ __($desc) }}
                </p>
            </div>
        </div>

        {{-- Right Side Status Badge --}}
        <div class="flex items-center gap-2 self-start sm:self-center flex-shrink-0 pt-2 sm:pt-0">
            <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-black/25 dark:bg-black/40 border border-orange-500/30 text-xs font-mono text-orange-100 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span class="font-sans font-medium text-white">{{ __('SRMS Verified') }}</span>
            </div>
        </div>

    </div>

</div>
