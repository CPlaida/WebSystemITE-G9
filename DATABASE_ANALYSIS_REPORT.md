# Database Schema Analysis Report

## Executive Summary
This report identifies tables that are not normalized, useless (no connections), or duplicates in the WebSystemITE-G9 database.

---

## 1. NOT NORMALIZED TABLES

### 1.1 `doctors` Table
**Issues:**
- ❌ **`specialization`** field is VARCHAR(100) instead of FK to `staff_specializations.id`
  - Should reference `staff_specializations` table
  - Currently stores free-text values instead of normalized data
  
- ❌ **`schedule`** field is TEXT (JSON) instead of using `doctor_schedules` table
  - Duplicates data that should be in `doctor_schedules`
  - Makes querying and maintaining schedules difficult

- ⚠️ **Redundant with `staff_profiles`**: The `doctors` table stores similar information to `staff_profiles` but is still actively used in:
  - `admission_details.attending_doctor_id` (FK to `doctors.id`)
  - Various controllers still reference `doctors` table

**Recommendation:** 
- Migrate `specialization` to FK relationship with `staff_specializations`
- Remove `schedule` TEXT field (use `doctor_schedules` table)
- Consider consolidating with `staff_profiles` or migrate all references

---

### 1.2 `patients` Table
**Issues:**
- ❌ **`vitals_bp`**, **`vitals_hr`**, **`vitals_temp`** fields duplicate `patient_vitals` table
  - These fields should only exist in `patient_vitals` table
  - Creates data inconsistency risk

- ⚠️ **`bed_id`** field may be redundant
  - `admission_details` table also has `bed_id`
  - Patient can have multiple admissions, so bed should be in `admission_details` only
  - `patients.bed_id` might be for current active admission only

**Recommendation:**
- Remove `vitals_bp`, `vitals_hr`, `vitals_temp` from `patients` table
- Consider if `patients.bed_id` is needed or should be derived from active `admission_details`

---

### 1.3 `doctor_schedules` Table
**Issues:**
- ❌ **`doctor_name`** field stores VARCHAR(255) instead of deriving from `users`/`staff_profiles`
  - Should be removed, use JOIN to get name from related tables
  - Creates data duplication and inconsistency risk

- ❌ **`department`** field stores VARCHAR(100) instead of FK to `staff_departments.id`
  - Should reference `staff_departments` table
  - Currently stores free-text values

**Recommendation:**
- Remove `doctor_name` field (derive from JOIN)
- Change `department` to `department_id` FK to `staff_departments`

---

### 1.4 `billing` Table
**Issues:**
- ⚠️ **Embedded PhilHealth fields** (philhealth_member, philhealth_suggested_amount, etc.)
  - While there's a `bill_philhealth_audits` table, billing also stores PhilHealth data
  - This might be intentional for audit trail, but creates duplication

- ⚠️ **Embedded HMO fields** (hmo_provider_id, hmo_member_no, etc.)
  - While there's an `hmo_authorizations` table, billing also stores HMO data
  - Similar duplication concern

**Recommendation:**
- Review if embedded fields are needed for historical snapshots
- If not, consider removing and using only the audit/authorization tables

---

## 2. USELESS TABLES (NO CONNECTIONS)

### 2.1 `nurses` Table
**Status:** ❌ **COMPLETELY UNUSED**

**Evidence:**
- Created in migration `2025-10-21-144602_CreateNurseTable.php`
- No models reference it
- No controllers query it
- Replaced by `staff_profiles` table (which handles all staff types)
- Only mentioned in comments/views, not actual database queries

**Recommendation:** 
- **DELETE** this table
- All nurse data should be in `staff_profiles` with appropriate `role_id`

---

### 2.2 `receptionists` Table
**Status:** ❌ **COMPLETELY UNUSED**

**Evidence:**
- Created in migration `2025-10-21-144654_CreateReceptionistTable.php`
- No models reference it
- No controllers query it
- Replaced by `staff_profiles` table
- No foreign key relationships to other tables

**Recommendation:**
- **DELETE** this table
- All receptionist data should be in `staff_profiles` with appropriate `role_id`

---

## 3. DUPLICATE/REDUNDANT TABLES

### 3.1 `doctors` vs `staff_profiles`
**Status:** ⚠️ **PARTIALLY DUPLICATE**

**Issue:**
- Both tables store doctor information
- `doctors` table is still actively used in:
  - `admission_details.attending_doctor_id` (FK constraint)
  - Various controllers (Doctor.php, Patients.php, Admissions.php)
- `staff_profiles` is the newer unified approach for all staff

**Current Usage:**
- `doctors` table: Still referenced in 24+ places in codebase
- `staff_profiles` table: Used for unified staff management

**Recommendation:**
- **Option A (Recommended):** Migrate all `doctors` references to use `staff_profiles`
  - Update `admission_details.attending_doctor_id` to reference `staff_profiles.id`
  - Update all controllers to use `staff_profiles` instead of `doctors`
  - Then delete `doctors` table
  
- **Option B:** Keep `doctors` as a specialized view/table for doctors only
  - Ensure proper normalization (FKs instead of VARCHARs)
  - Sync with `staff_profiles` to avoid duplication

---

### 3.2 `nurses` vs `staff_profiles`
**Status:** ❌ **COMPLETE DUPLICATE (nurses unused)**

**Issue:**
- `nurses` table duplicates functionality of `staff_profiles`
- `nurses` is completely unused
- `staff_profiles` handles all staff types including nurses

**Recommendation:**
- **DELETE** `nurses` table (already identified as useless)

---

### 3.3 `receptionists` vs `staff_profiles`
**Status:** ❌ **COMPLETE DUPLICATE (receptionists unused)**

**Issue:**
- `receptionists` table duplicates functionality of `staff_profiles`
- `receptionists` is completely unused
- `staff_profiles` handles all staff types including receptionists

**Recommendation:**
- **DELETE** `receptionists` table (already identified as useless)

---

## 4. SUMMARY OF ACTIONS NEEDED

### High Priority (Data Integrity Issues)
1. ✅ **Remove `nurses` table** - Completely unused, safe to delete
2. ✅ **Remove `receptionists` table** - Completely unused, safe to delete
3. ⚠️ **Normalize `doctors.specialization`** - Change to FK to `staff_specializations`
4. ⚠️ **Remove `doctors.schedule`** - Use `doctor_schedules` table instead
5. ⚠️ **Remove `patients.vitals_*` fields** - Use `patient_vitals` table only

### Medium Priority (Code Cleanup)
6. ⚠️ **Normalize `doctor_schedules.doctor_name`** - Remove, use JOIN
7. ⚠️ **Normalize `doctor_schedules.department`** - Change to FK `department_id`
8. ⚠️ **Resolve `doctors` vs `staff_profiles` duplication** - Choose one approach

### Low Priority (Review Needed)
9. ⚠️ **Review `billing` embedded fields** - Determine if PhilHealth/HMO fields needed
10. ⚠️ **Review `patients.bed_id`** - Determine if needed or should be derived

---

## 5. TABLES WITH NO FOREIGN KEY RELATIONSHIPS

### Tables with Missing FKs:
- `nurses` - No FKs to other tables (and unused)
- `receptionists` - No FKs to other tables (and unused)
- `doctor_schedules.service_id` - References `services` but no FK constraint defined
- `laboratory.service_id` - References `services` but no FK constraint defined
- `billing.service_id` - References `services` but no FK constraint defined

**Note:** Some missing FKs might be intentional (e.g., services table created later), but should be added for data integrity.

---

## 6. RECOMMENDED MIGRATION PLAN

### Phase 1: Remove Unused Tables (Safe)
1. Create migration to drop `nurses` table
2. Create migration to drop `receptionists` table

### Phase 2: Normalize Existing Tables
1. Remove `patients.vitals_*` fields
2. Normalize `doctors.specialization` to FK
3. Remove `doctors.schedule` field
4. Normalize `doctor_schedules.doctor_name` (remove)
5. Normalize `doctor_schedules.department` to FK

### Phase 3: Consolidate Duplicate Tables
1. Migrate all `doctors` references to `staff_profiles`
2. Update `admission_details.attending_doctor_id` FK
3. Drop `doctors` table after migration complete

### Phase 4: Add Missing Foreign Keys
1. Add FK constraints for `service_id` fields
2. Review and add any other missing FKs

---

**Report Generated:** Based on analysis of all migration files and codebase references
**Total Tables Analyzed:** 24 tables
**Issues Found:** 10 major issues across normalization, duplicates, and unused tables


