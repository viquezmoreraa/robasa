# Graph Report - robasa-1  (2026-10-21)

## Corpus Check
- 148 files · ~71,929 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 20 file(s) not represented in the graph (top: (none) 16, .example 1, .xml 1)

## Summary
- 738 nodes · 1261 edges · 89 communities (16 shown, 73 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 10 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `5b3d4722`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Combinacion
- composer.json
- MotorPsicometrico
- package.json
- .estrategiaPara
- ResultadoFinal
- User
- Illuminate\Database\Schema\Blueprint
- ResultadoDimension
- database/migrations/2026_10_09_000001_create_pruebas_table.php
- bootstrap/app.php
- public/app/Services/Psicometria/Estrategias/Combinacion.php
- AppServiceProvider
- AsignacionPrueba
- UtilesEstrategia
- 2026_10_20_000000_agregar_fk_postulacion_a_asignaciones_prueba. pendiente.php
- 0001_01_01_000001_create_cache_table.php
- EstrategiasResultadoTest
- RegistroRequest
- database/database/migrations/2026_10_09_000001_create_pruebas_table.php
- Illuminate\Support\Facades\Schema
- database/database/migrations/2026_10_09_000004_create_opciones_table.php
- database/database/migrations/2026_10_09_000005_create_opcion_dimension_table.php
- database/database/migrations/2026_10_09_000002_create_dimensiones_table.php
- 2026_10_20_000000_add_metodo_dicotomia_combinada.php
- views.js
- Illuminate\Http\Request
- database/database/migrations/2026_10_09_000010_create_resultados_dimension_table.php
- database/migrations/2026_10_09_000002_create_dimensiones_table.php
- Illuminate\Database\Seeder
- README.md
- Postulacion
- database/migrations/2026_10_09_000006_create_perfiles_table.php
- public/app/Services/Psicometria/MotorPsicometrico.php
- Illuminate\Support\Facades\DB
- database/migrations/2026_10_09_000011_create_resultados_prueba_table.php
- ResultadoDimension
- DimensionMayor
- web.php
- ResultadoFinal
- 2026_10_13_000001_add_dimension_padre_to_dimensiones_table.php
- 2026_10_20_000000_agregar_fk_postulacion_a_asignaciones_prueba.php
- database/database/migrations/2026_10_09_000011_create_resultados_prueba_table.php
- 2026_10_15_000001_add_metodo_patron_disc.php
- database/migrations/2026_10_09_000010_create_resultados_dimension_table.php
- Illuminate\Database\Migrations\Migration
- database/migrations/2026_10_09_000004_create_opciones_table.php
- CLAUDE.md
- login.blade.php
- Illuminate\Foundation\Testing\RefreshDatabase
- AutenticacionTest
- postulante/index.blade.php
- 2026_10_15_000002_create_disc_clasificacion_table.php
- 2026_10_20_000001_add_username_to_users_table.php
- TestCase
- LoginRequest
- 0001_01_01_000002_create_jobs_table.php
- TalentoHumanoController.php
- 2026_10_19_000000_create_rangos_interpretacion_table.php
- database/database/migrations/2026_10_09_000008_create_asignaciones_prueba_table.php
- formulario.blade.php
- database/database/migrations/2026_10_09_000009_create_respuestas_table.php
- PuntajePorDimension
- PostulanteResponderEdTest.php
- PostulanteResponderBarsitTest
- PostulanteResponderEdTest
- PostulanteResponderPruebaTest
- 2026_10_21_000001_renombrar_rol_contratista_a_talento_humano.php
- 2026_10_21_000002_add_estado_to_postulaciones_table.php
- ComunicacionSeeder.php
- database/database/migrations/2026_10_09_000007_create_rangos_interpretacion_table.php
- database/migrations/2026_10_09_000009_create_respuestas_table.php
- 2026_10_13_000002_create_baremos_table.php
- prueba.blade.php
- talento-humano/index.blade.php
- show.blade.php

## God Nodes (most connected - your core abstractions)
1. `ResultadoFinal` - 34 edges
2. `ResultadoDimension` - 24 edges
3. `MotorPsicometrico` - 23 edges
4. `Postulacion` - 21 edges
5. `User` - 18 edges
6. `EstrategiaResultado` - 18 edges
7. `TestCase` - 18 edges
8. `AsignacionPrueba` - 17 edges
9. `Combinacion` - 15 edges
10. `PatronDisc` - 15 edges

## Surprising Connections (you probably didn't know these)
- `{closure#7}()` --references--> `ResultadoDimension`  [EXTRACTED]
  public/app/Services/Psicometria/MotorPsicometrico.php → app/Services/Psicometria/ResultadoDimension.php
- `Combinacion` --implements--> `EstrategiaResultado`  [EXTRACTED]
  public/app/Services/Psicometria/Estrategias/Combinacion.php → app/Services/Psicometria/Estrategias/EstrategiaResultado.php
- `TotalCorrectas` --implements--> `EstrategiaResultado`  [EXTRACTED]
  public/app/Services/Psicometria/Estrategias/TotalCorrectas.php → app/Services/Psicometria/Estrategias/EstrategiaResultado.php
- `Combinacion` --mixes_in--> `UtilesEstrategia`  [EXTRACTED]
  public/app/Services/Psicometria/Estrategias/Combinacion.php → app/Services/Psicometria/Estrategias/UtilesEstrategia.php
- `DimensionMayor` --mixes_in--> `UtilesEstrategia`  [EXTRACTED]
  public/app/Services/Psicometria/Estrategias/DimensionMayor.php → app/Services/Psicometria/Estrategias/UtilesEstrategia.php

## Import Cycles
- None detected.

## Communities (89 total, 73 thin omitted)

### Community 1 - "composer.json"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

### Community 2 - "MotorPsicometrico"
Cohesion: 0.13
Nodes (8): VerificarBarsit, VerificarBigFive, VerificarComunicacion, VerificarDisc, VerificarEd, VerificarEscuchaActiva, VerificarKeirsey, MotorPsicometrico

### Community 3 - "package.json"
Cohesion: 0.10
Nodes (19): devDependencies, axios, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, private (+11 more)

### Community 5 - "ResultadoFinal"
Cohesion: 0.11
Nodes (6): EstrategiaResultado, ResultadoFinal, DimensionMayor, EstrategiaResultado, PuntajePorDimension, PuntajeT

### Community 7 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.13
Nodes (14): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}() (+6 more)

### Community 8 - "ResultadoDimension"
Cohesion: 0.06
Nodes (12): DicotomiaCombinada, PatronDisc, PuntajeT, {closure#1}(), {closure#6}(), ResultadoDimension, {closure#1}(), UtilesEstrategia (+4 more)

### Community 20 - "RegistroRequest"
Cohesion: 0.09
Nodes (4): EsTalentoHumano, RegistroRequest, CorreoProveedorPermitido, UserFactory

### Community 22 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 27 - "views.js"
Cohesion: 0.14
Nodes (27): api, API_BASE, main, router(), clearWizard(), loadWizard(), saveWizard(), setVacanteId() (+19 more)

### Community 31 - "Illuminate\Database\Seeder"
Cohesion: 0.15
Nodes (5): BigFiveSeeder, DatabaseSeeder, DiscSeeder, {closure#1}(), EscuchaActivaSeeder

### Community 32 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 36 - "Illuminate\Support\Facades\DB"
Cohesion: 0.21
Nodes (4): BarsitSeeder, {closure#1}(), {closure#1}(), KeirseySeeder

### Community 54 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 66 - "TestCase"
Cohesion: 0.29
Nodes (3): ExampleTest, TalentoHumanoVeResultadosTest, TestCase

### Community 69 - "0001_01_01_000002_create_jobs_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 80 - "2026_10_21_000001_renombrar_rol_contratista_a_talento_humano.php"
Cohesion: 0.60
Nodes (4): cambiarRol(), {closure#1}(), down(), up()

## Knowledge Gaps
- **9 isolated node(s):** `axios`, `concurrently`, `tailwindcss`, `layouts.app`, `layouts.app` (+4 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 278 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **73 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Support\Facades\DB`, `PostulanteResponderEdTest.php`, `RegistroRequest`, `Illuminate\Foundation\Testing\RefreshDatabase`, `AutenticacionTest`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **What connects `axios`, `concurrently`, `tailwindcss` to the rest of the system?**
  _9 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.041666666666666664 - nodes in this community are weakly interconnected._
- **Why does `EstrategiaResultado` connect `ResultadoFinal` to `Combinacion`, `MotorPsicometrico`, `public/app/Services/Psicometria/MotorPsicometrico.php`, `.estrategiaPara`, `ResultadoDimension`, `DimensionMayor`, `PuntajePorDimension`, `public/app/Services/Psicometria/Estrategias/Combinacion.php`, `UtilesEstrategia`?**
  _High betweenness centrality (0.042) - this node is a cross-community bridge._
- **Should `MotorPsicometrico` be split into smaller, more focused modules?**
  _Cohesion score 0.12643678160919541 - nodes in this community are weakly interconnected._
- **Why does `MotorPsicometrico` connect `MotorPsicometrico` to `ResultadoDimension`, `AsignacionPrueba`?**
  _High betweenness centrality (0.041) - this node is a cross-community bridge._
- **Should `package.json` be split into smaller, more focused modules?**
  _Cohesion score 0.10476190476190476 - nodes in this community are weakly interconnected._