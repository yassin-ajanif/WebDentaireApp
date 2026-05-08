# Financial Tracking and Audit System Plan

This document outlines the strategy for implementing a robust financial tracking and audit system to ensure the "Total à remettre au médecin" (Daily Total) is accurate and transparent.

## 1. Core Principles
- **No Data Deletion**: Replace all "Delete" actions with "Cancel" or "Log and Remove" to maintain a permanent audit trail.
- **Daily Impact Tracking**: Financial adjustments are tracked based on the day they *occurred*, even if they refer to past treatments/sessions.
- **Cash Flow Integrity**: Every change affecting "Cash in Hand" must be explicitly logged and explained.

## 2. Phase 1: Disabling Deletions
### 2.1 Session Deletions
- **Action**: Disable the `deleteSession` method and UI button.
- **Replacement**: Implement a "Cancel Session" workflow. 
- **Requirement**: When a session is "removed," its status must be updated to "cancelled" in the `treatment_sessions` table to maintain a permanent audit trail. No reason is required for session cancellation.

### 2.2 Treatment Deletions
- **Status**: Already replaced with "Cancel Treatment" in the `treatment_infos` table.
- **Enhancement**: Add `cancelled_at` timestamp to track *when* the cancellation impact should hit the Chronology.

## 3. Phase 2: The Quadruple-Audit Pillars
The system will monitor four types of events that impact the doctor's daily total:

1.  **Cancellations (Refunds)**: 
    - **Logic**: If a treatment is cancelled today, the sum of all its prior payments is calculated as a negative impact for today.
    - **Data**: `treatment_infos.cancelled_at`.

2.  **Session Deletions (Cancellations)**: 
    - **Logic**: Any payment cancelled today is tracked as a negative impact.
    - **Data**: `treatment_sessions.status` and `treatment_sessions.cancelled_at`.

3.  **Session Corrections**: 
    - **Logic**: Differences (Deltas) between `old_received_payment` and `new_received_payment` for edits made today.
    - **Data**: `treatment_session_corrections`.

4.  **Treatment Corrections**: 
    - **Logic**: Tracking changes to `global_price` today.
    - **Data**: `treatment_corrections`.

## 4. Phase 3: Daily Total Logic (Chronology)
The function `calculateActualDailyTotal($date)` will be implemented as:
`Handover = (Today's Active Sessions) + (Today's Correction Deltas) - (Today's Cancellations) - (Today's Session Cancellations)`

## 5. Phase 4: UI Implementation
Add an "Audit and Adjustments" section below the Chronology table to show the doctor:
- Summary of today's payments.
- Detailed list of refunds (cancellations).
- Detailed list of session cancellations.
- Detailed list of corrections.
- Final "True Total."
