# Graph Report - .  (2026-06-04)

## Corpus Check
- Large corpus: 614 files · ~1,766,897 words. Semantic extraction will be expensive (many Claude tokens). Consider running on a subfolder.

## Summary
- 1310 nodes · 2022 edges · 164 communities (135 shown, 29 thin omitted)
- Extraction: 91% EXTRACTED · 9% INFERRED · 0% AMBIGUOUS · INFERRED: 181 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- [[_COMMUNITY_Custom Subscription Controller|Custom Subscription Controller]]
- [[_COMMUNITY_Demo Data & Store State|Demo Data & Store State]]
- [[_COMMUNITY_Authorization Policies|Authorization Policies]]
- [[_COMMUNITY_Store Categories Resource|Store Categories Resource]]
- [[_COMMUNITY_Store Brands Resource|Store Brands Resource]]
- [[_COMMUNITY_Dashboard & Payments Overview|Dashboard & Payments Overview]]
- [[_COMMUNITY_Authentication Flow|Authentication Flow]]
- [[_COMMUNITY_Client Dashboard|Client Dashboard]]
- [[_COMMUNITY_Floor Occupation Map|Floor Occupation Map]]
- [[_COMMUNITY_Products Resource|Products Resource]]
- [[_COMMUNITY_Record View Pages|Record View Pages]]
- [[_COMMUNITY_Subscription Billing Resource|Subscription Billing Resource]]
- [[_COMMUNITY_Users Management Resource|Users Management Resource]]
- [[_COMMUNITY_Store Spaces Resource|Store Spaces Resource]]
- [[_COMMUNITY_Record Creation Pages|Record Creation Pages]]
- [[_COMMUNITY_Record Edit Pages|Record Edit Pages]]
- [[_COMMUNITY_Rental Pricing Models|Rental Pricing Models]]
- [[_COMMUNITY_Record List Pages|Record List Pages]]
- [[_COMMUNITY_Subscription Payments Resource|Subscription Payments Resource]]
- [[_COMMUNITY_Role Access Policies|Role Access Policies]]
- [[_COMMUNITY_Audit & Account Status|Audit & Account Status]]
- [[_COMMUNITY_Dashboard Chart Widgets|Dashboard Chart Widgets]]
- [[_COMMUNITY_User Authentication Model|User Authentication Model]]
- [[_COMMUNITY_Product Access Policies|Product Access Policies]]
- [[_COMMUNITY_Payment Access Policies|Payment Access Policies]]
- [[_COMMUNITY_Module Group 25|Module Group 25]]
- [[_COMMUNITY_Module Group 26|Module Group 26]]
- [[_COMMUNITY_Module Group 27|Module Group 27]]
- [[_COMMUNITY_Module Group 28|Module Group 28]]
- [[_COMMUNITY_Module Group 29|Module Group 29]]
- [[_COMMUNITY_Module Group 30|Module Group 30]]
- [[_COMMUNITY_Module Group 31|Module Group 31]]
- [[_COMMUNITY_Module Group 32|Module Group 32]]
- [[_COMMUNITY_Module Group 33|Module Group 33]]
- [[_COMMUNITY_Module Group 34|Module Group 34]]
- [[_COMMUNITY_Module Group 35|Module Group 35]]
- [[_COMMUNITY_Module Group 36|Module Group 36]]
- [[_COMMUNITY_Module Group 37|Module Group 37]]
- [[_COMMUNITY_Module Group 38|Module Group 38]]
- [[_COMMUNITY_Module Group 39|Module Group 39]]
- [[_COMMUNITY_Module Group 40|Module Group 40]]
- [[_COMMUNITY_Module Group 41|Module Group 41]]
- [[_COMMUNITY_Module Group 42|Module Group 42]]
- [[_COMMUNITY_Module Group 43|Module Group 43]]
- [[_COMMUNITY_Module Group 44|Module Group 44]]
- [[_COMMUNITY_Module Group 45|Module Group 45]]
- [[_COMMUNITY_Module Group 46|Module Group 46]]
- [[_COMMUNITY_Module Group 47|Module Group 47]]
- [[_COMMUNITY_Module Group 48|Module Group 48]]
- [[_COMMUNITY_Module Group 49|Module Group 49]]
- [[_COMMUNITY_Module Group 50|Module Group 50]]
- [[_COMMUNITY_Module Group 51|Module Group 51]]
- [[_COMMUNITY_Module Group 52|Module Group 52]]
- [[_COMMUNITY_Module Group 53|Module Group 53]]
- [[_COMMUNITY_Module Group 54|Module Group 54]]
- [[_COMMUNITY_Module Group 55|Module Group 55]]
- [[_COMMUNITY_Module Group 56|Module Group 56]]
- [[_COMMUNITY_Module Group 57|Module Group 57]]
- [[_COMMUNITY_Module Group 58|Module Group 58]]
- [[_COMMUNITY_Module Group 59|Module Group 59]]
- [[_COMMUNITY_Module Group 60|Module Group 60]]
- [[_COMMUNITY_Module Group 61|Module Group 61]]
- [[_COMMUNITY_Module Group 62|Module Group 62]]
- [[_COMMUNITY_Module Group 63|Module Group 63]]
- [[_COMMUNITY_Module Group 64|Module Group 64]]
- [[_COMMUNITY_Module Group 65|Module Group 65]]
- [[_COMMUNITY_Module Group 66|Module Group 66]]
- [[_COMMUNITY_Module Group 67|Module Group 67]]
- [[_COMMUNITY_Module Group 68|Module Group 68]]
- [[_COMMUNITY_Module Group 69|Module Group 69]]
- [[_COMMUNITY_Module Group 70|Module Group 70]]
- [[_COMMUNITY_Module Group 71|Module Group 71]]
- [[_COMMUNITY_Module Group 72|Module Group 72]]
- [[_COMMUNITY_Module Group 74|Module Group 74]]
- [[_COMMUNITY_Module Group 116|Module Group 116]]
- [[_COMMUNITY_Module Group 117|Module Group 117]]
- [[_COMMUNITY_Module Group 120|Module Group 120]]
- [[_COMMUNITY_Module Group 121|Module Group 121]]
- [[_COMMUNITY_Module Group 122|Module Group 122]]
- [[_COMMUNITY_Module Group 123|Module Group 123]]
- [[_COMMUNITY_Module Group 124|Module Group 124]]

## God Nodes (most connected - your core abstractions)
1. `InfraestructurasTiendas` - 38 edges
2. `ClientDashboardController` - 25 edges
3. `Controller` - 21 edges
4. `ActiveInfraestructura` - 21 edges
5. `CreateInfraestructurasCustom` - 19 edges
6. `EditInfraestructurasCustom` - 19 edges
7. `Clientes` - 19 edges
8. `ListSuscripcionesTarifas` - 16 edges
9. `SuscripcionesTarifas` - 16 edges
10. `User` - 15 edges

## Surprising Connections (you probably didn't know these)
- `SuscripcionCustomController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Admin/SuscripcionCustomController.php → app/Http/Controllers/Controller.php
- `ClientDashboardController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Cliente/ClientDashboardController.php → app/Http/Controllers/Controller.php
- `DirectorioController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/DirectorioController.php → app/Http/Controllers/Controller.php
- `WelcomeController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/WelcomeController.php → app/Http/Controllers/Controller.php
- `ForgotPasswordController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Auth/ForgotPasswordController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (164 total, 29 thin omitted)

### Community 0 - "Custom Subscription Controller"
Cohesion: 0.06
Nodes (20): SuscripcionCustomController, Schema, Request, Request, BelongsTo, BelongsToMany, Carbon, HasMany (+12 more)

### Community 1 - "Demo Data & Store State"
Cohesion: 0.09
Nodes (15): Carbon, Suscripciones, SuscripcionesCobros, SuscripcionesPagos, EstadoTienda, Seeder, AdministradoresSeeder, ClientesSeeder (+7 more)

### Community 2 - "Authorization Policies"
Cohesion: 0.10
Nodes (9): AuthUser, Infraestructuras, AuthUser, SuscripcionesCobros, AuthUser, HandlesAuthorization, InfraestructurasPolicy, SuscripcionesCobrosPolicy (+1 more)

### Community 3 - "Store Categories Resource"
Cohesion: 0.08
Nodes (15): BackedEnum, Builder, Schema, Table, UnitEnum, Schema, Schema, Table (+7 more)

### Community 4 - "Store Brands Resource"
Cohesion: 0.09
Nodes (14): BackedEnum, Schema, Table, UnitEnum, Schema, Schema, Table, AuthUser (+6 more)

### Community 5 - "Dashboard & Payments Overview"
Cohesion: 0.08
Nodes (10): Schema, Builder, Table, AuthUser, BaseWidget, Clientes, ClientesPolicy, SuscripcionesPagosForm (+2 more)

### Community 6 - "Authentication Flow"
Cohesion: 0.09
Nodes (11): Request, Request, Request, ForgotPasswordController, LoginController, ResetPasswordController, Controller, ReporteCobrosController (+3 more)

### Community 7 - "Client Dashboard"
Cohesion: 0.15
Nodes (4): Request, BelongsTo, ClientDashboardController, ProductosImagenes

### Community 8 - "Floor Occupation Map"
Cohesion: 0.09
Nodes (8): BackedEnum, Htmlable, UnitEnum, Infraestructuras, InfraestructurasPisos, MapaOcupacion, TiendasPropietarios, OcupacionPorPisoChart

### Community 9 - "Products Resource"
Cohesion: 0.09
Nodes (12): BackedEnum, Builder, Schema, Table, UnitEnum, Schema, Schema, Table (+4 more)

### Community 10 - "Record View Pages"
Cohesion: 0.10
Nodes (9): ViewCategorias, ViewClienteCustom, ViewClientes, ViewInfraestructuras, ViewProductos, ViewSuscripcionesCobros, ViewSuscripcionesPagos, ViewUsuarios (+1 more)

### Community 11 - "Subscription Billing Resource"
Cohesion: 0.10
Nodes (12): Schema, Schema, BackedEnum, Builder, Schema, Table, UnitEnum, Table (+4 more)

### Community 12 - "Users Management Resource"
Cohesion: 0.10
Nodes (12): Schema, Schema, Table, BackedEnum, Builder, Schema, Table, UnitEnum (+4 more)

### Community 13 - "Store Spaces Resource"
Cohesion: 0.11
Nodes (11): BackedEnum, Schema, Table, UnitEnum, Schema, Schema, Table, InfraestructurasResource (+3 more)

### Community 14 - "Record Creation Pages"
Cohesion: 0.11
Nodes (8): CreateRecord, CreateCategorias, CreateInfraestructuras, CreateProductos, CreateSuscripciones, CreateSuscripcionesCobros, CreateSuscripcionesTarifas, CreateUsuarios

### Community 15 - "Record Edit Pages"
Cohesion: 0.12
Nodes (8): EditRecord, EditCategorias, EditInfraestructuras, EditMarcas, EditSuscripciones, EditSuscripcionesCobros, EditSuscripcionesTarifas, EditUsuarios

### Community 16 - "Rental Pricing Models"
Cohesion: 0.23
Nodes (5): LogOptions, DescuentoTiempo, TamanoEtiqueta, TamanoPrecio, ListSuscripcionesTarifas

### Community 17 - "Record List Pages"
Cohesion: 0.12
Nodes (7): ListRecords, ListCategorias, ListClientes, ListInfraestructuras, ListMarcas, ListSuscripcionesCobros, ListUsuarios

### Community 18 - "Subscription Payments Resource"
Cohesion: 0.13
Nodes (10): Schema, BackedEnum, Builder, Schema, Table, UnitEnum, Table, SuscripcionesPagosInfolist (+2 more)

### Community 19 - "Role Access Policies"
Cohesion: 0.21
Nodes (4): AuthUser, RolePolicy, Role, RolesAndPermissionsSeeder

### Community 20 - "Audit & Account Status"
Cohesion: 0.17
Nodes (9): BackedEnum, Table, UnitEnum, BackedEnum, Table, HasTable, InteractsWithTable, Auditoria (+1 more)

### Community 21 - "Dashboard Chart Widgets"
Cohesion: 0.13
Nodes (3): ChartWidget, CostoOportunidadVacanciaChart, MetodoPagoChart

### Community 22 - "User Authentication Model"
Cohesion: 0.19
Nodes (10): LogOptions, Panel, Authenticatable, FilamentUser, HasFactory, HasName, HasRoles, User (+2 more)

### Community 23 - "Product Access Policies"
Cohesion: 0.27
Nodes (3): AuthUser, ProductosPolicy, Productos

### Community 24 - "Payment Access Policies"
Cohesion: 0.27
Nodes (3): AuthUser, SuscripcionesPagos, SuscripcionesPagosPolicy

### Community 25 - "Module Group 25"
Cohesion: 0.27
Nodes (3): AuthUser, Suscripciones, SuscripcionesPolicy

### Community 26 - "Module Group 26"
Cohesion: 0.15
Nodes (4): Page, CreateUsuariosCustom, EditUsuariosCustom, ListInfraestructurasCustom

### Community 27 - "Module Group 27"
Cohesion: 0.14
Nodes (4): Table, CreateSuscripcionesPagos, EditSuscripcionesPagos, ListSuscripcionesPagos

### Community 28 - "Module Group 28"
Cohesion: 0.21
Nodes (7): BelongsTo, HasMany, LogOptions, LogsActivity, Model, ClientesDocumentos, Infraestructuras

### Community 29 - "Module Group 29"
Cohesion: 0.24
Nodes (4): BelongsTo, HasMany, LogOptions, Suscripciones

### Community 33 - "Module Group 33"
Cohesion: 0.21
Nodes (6): BackedEnum, Htmlable, Schema, UnitEnum, HasForms, SimuladorAlquiler

### Community 34 - "Module Group 34"
Cohesion: 0.18
Nodes (3): Infraestructuras, SeleccionarInfraestructura, ActiveInfraestructura

### Community 35 - "Module Group 35"
Cohesion: 0.20
Nodes (3): Model, CreateClientes, WithFileUploads

### Community 36 - "Module Group 36"
Cohesion: 0.24
Nodes (6): BackedEnum, Builder, Schema, Table, UnitEnum, SuscripcionesResource

### Community 37 - "Module Group 37"
Cohesion: 0.25
Nodes (7): BackedEnum, Builder, Schema, Table, UnitEnum, Resource, SuscripcionesTarifasResource

### Community 38 - "Module Group 38"
Cohesion: 0.27
Nodes (4): BelongsTo, HasMany, LogOptions, Clientes

### Community 39 - "Module Group 39"
Cohesion: 0.29
Nodes (4): BelongsTo, HasMany, LogOptions, Productos

### Community 40 - "Module Group 40"
Cohesion: 0.24
Nodes (4): BelongsTo, HasMany, LogOptions, SuscripcionesCobros

### Community 41 - "Module Group 41"
Cohesion: 0.31
Nodes (4): Suscripciones, Builder, SuscripcionObserver, self

### Community 42 - "Module Group 42"
Cohesion: 0.20
Nodes (9): addPiso, addTienda({{ $pIndex }}), closeBackgroundModal, confirmBackgroundImage, openBackgroundModal({{ $pIndex }}), removePiso({{ $pIndex }}), removeTienda({{ $pIndex }}, {{ $tIndex }}), save (+1 more)

### Community 43 - "Module Group 43"
Cohesion: 0.29
Nodes (5): BackedEnum, Schema, Table, UnitEnum, ClientesResource

### Community 45 - "Module Group 45"
Cohesion: 0.29
Nodes (4): BelongsTo, HasMany, LogOptions, InfraestructurasPisos

### Community 46 - "Module Group 46"
Cohesion: 0.29
Nodes (5): BelongsTo, BelongsToMany, LogOptions, Marcas, SoftDeletes

### Community 47 - "Module Group 47"
Cohesion: 0.31
Nodes (3): UserFactory, Factory, static

### Community 49 - "Module Group 49"
Cohesion: 0.33
Nodes (4): BelongsTo, HasMany, LogOptions, Categorias

### Community 50 - "Module Group 50"
Cohesion: 0.31
Nodes (4): BelongsTo, LogOptions, HasOneThrough, SuscripcionesPagos

### Community 52 - "Module Group 52"
Cohesion: 0.25
Nodes (5): BackedEnum, Htmlable, Table, UnitEnum, BalanceSuscripciones

### Community 53 - "Module Group 53"
Cohesion: 0.25
Nodes (4): BackedEnum, Table, UnitEnum, ReporteMorosidad

### Community 54 - "Module Group 54"
Cohesion: 0.25
Nodes (7): cancelEdit, deleteDescuento({{ $d->id }}), deleteEtiqueta({{ $e->id }}), deletePrecio({{ $p->id }}), editDescuento({{ $d->id }}), editEtiqueta({{ $e->id }}), editPrecio({{ $p->id }})

### Community 56 - "Module Group 56"
Cohesion: 0.43
Nodes (3): Infraestructuras, InteractsWithForms, InfraestructurasPisosDiseno

### Community 64 - "Module Group 64"
Cohesion: 0.60
Nodes (3): Panel, AdminPanelProvider, PanelProvider

### Community 72 - "Module Group 72"
Cohesion: 0.50
Nodes (3): currentMonth, nextMonth, previousMonth

## Knowledge Gaps
- **64 isolated node(s):** `BackedEnum`, `UnitEnum`, `BackedEnum`, `UnitEnum`, `Htmlable` (+59 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **29 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `InfraestructurasTiendas` connect `Custom Subscription Controller` to `Demo Data & Store State`, `Dashboard & Payments Overview`, `Floor Occupation Map`, `Module Group 41`, `Products Resource`, `Store Spaces Resource`, `Module Group 28`?**
  _High betweenness centrality (0.106) - this node is a cross-community bridge._
- **Why does `Clientes` connect `Dashboard & Payments Overview` to `Custom Subscription Controller`, `Demo Data & Store State`, `Client Dashboard`, `Floor Occupation Map`, `Products Resource`, `Module Group 43`?**
  _High betweenness centrality (0.081) - this node is a cross-community bridge._
- **Why does `ActiveInfraestructura` connect `Module Group 34` to `Custom Subscription Controller`, `Dashboard & Payments Overview`, `Floor Occupation Map`, `Module Group 73`, `Module Group 41`, `Dashboard Chart Widgets`, `Module Group 55`, `Module Group 57`, `Module Group 61`?**
  _High betweenness centrality (0.060) - this node is a cross-community bridge._
- **Are the 23 inferred relationships involving `InfraestructurasTiendas` (e.g. with `.create()` and `.getTiendaPrecio()`) actually correct?**
  _`InfraestructurasTiendas` has 23 INFERRED edges - model-reasoned connections that need verification._
- **What connects `BackedEnum`, `UnitEnum`, `BackedEnum` to the rest of the system?**
  _64 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Custom Subscription Controller` be split into smaller, more focused modules?**
  _Cohesion score 0.05779220779220779 - nodes in this community are weakly interconnected._
- **Should `Demo Data & Store State` be split into smaller, more focused modules?**
  _Cohesion score 0.09090909090909091 - nodes in this community are weakly interconnected._