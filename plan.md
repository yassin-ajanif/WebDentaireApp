Laravel Migration Feature Handoff Plan

Goal

Produce a concise, implementation-ready feature specification of the current app and a Laravel-target architecture map, so an AI model can rebuild the product with parity.

Current App Scope (Source of Truth)





Stack and architecture:





Desktop MVVM app in .NET/Avalonia with EF Core persistence (SQLite/SQL Server) and layered projects.



References: DentaireApp.sln, src/DentaireApp.UI.Avalonia/Program.cs, src/DentaireApp.Bootstrap/DependencyInjection/ServiceCollectionExtensions.cs



Core domain entities (implemented):





Patient (identity/contact), Appointment (queue ticket/status/timestamps), TreatmentInfo (care line items + financial fields).



References: src/DentaireApp.Business/Models/Patients/Patient.cs, src/DentaireApp.Business/Models/Appointments/Appointment.cs, src/DentaireApp.Business/Models/Patients/TreatmentInfo.cs, src/DentaireApp.DataAccess.EFCore/Persistence/AppDbContext.cs



Main implemented features:





Queue management: create queue number, update statuses (Waiting, InProgress, Done, Cancelled), queue search/filter, predicted consultation time.



Patient management: patient list/search/pagination/delete, duplicate check by telephone when adding from queue.



Patient record: edit patient details and treatment lines (add/remove/save).



Settings: queue timing settings persisted to local JSON.



References: src/DentaireApp.UI.Avalonia/ViewModels/QueueViewModel.cs, src/DentaireApp.Business/Services/QueueService.cs, src/DentaireApp.Business/Services/AppointmentService.cs, src/DentaireApp.UI.Avalonia/ViewModels/MainWindowViewModel.cs, src/DentaireApp.Business/Services/PatientRecordService.cs, src/DentaireApp.UI.Avalonia/Services/UiSettingsService.cs



Explicit non-scope (currently not implemented):





No auth/roles.



No external API integrations.



No background worker/scheduler.



Billing and odontogram screens are placeholders only.



References: src/DentaireApp.UI.Avalonia/ViewModels/BillingViewModel.cs, src/DentaireApp.UI.Avalonia/ViewModels/OdontogramViewModel.cs

Laravel Parity Target (What AI Should Build)





Backend modules:





Patients CRUD + phone uniqueness.



Appointments queue lifecycle + status transitions + timestamps.



TreatmentInfos CRUD nested under patient.



QueuePrediction service using configurable average consultation duration and anchor strategy.



Settings persistence for queue prediction parameters.



Database design (initial):





patients, appointments, treatment_infos, and settings tables.



FK constraints and delete rules equivalent to EF model (restrict cascade where required).



Add unique index on patients.telephone.



API/UI contract target:





Endpoints/resources for queue list/actions, patient listing/search, patient record read/write, and settings update.



Keep billing/odontogram as deferred modules unless user explicitly requests phase 2.

Suggested Delivery Phases





Foundation: Laravel project setup, migrations, seeders, Eloquent models, policies/validation.



Queue core: queue APIs + status transition rules + prediction service.



Patient dossier: patient CRUD + nested treatment info workflows.



Settings + UX parity: queue timing settings + dialogs/feedback equivalents.



Stabilization: feature tests, regression tests, import/migration scripts (if needed).

Acceptance Criteria for “Parity v1”





Can create/search/manage queue entries and change statuses with valid transition behavior.



Can create/search/edit/delete patients with telephone uniqueness preserved.



Can edit patient treatment lines and compute/display financial fields as in current behavior.



Queue time prediction works from current queue state and configured settings.



Placeholder modules remain out of scope unless explicitly added.

Handoff Notes for the Next AI Model





Treat current C# source files as behavioral truth over old planning notes.



Do not invent auth, billing, payment gateway, or notification systems in v1.



Preserve business constraints from EF model and service layer first; then optimize architecture.



If ambiguity appears, mirror the behavior from these files first: src/DentaireApp.Business/Services/AppointmentService.cs, src/DentaireApp.Business/Services/QueueService.cs, src/DentaireApp.Business/Services/PatientRecordService.cs.

