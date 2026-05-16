# User Acceptance Testing (UAT) Guide - Logistics Module

## Overview
The Logistics module handles the procurement lifecycle from Purchase Request (PR) to Purchase Order (PO) and fulfillment tracking. It integrates with the MasterData module for vendors, items, and digital signatures.

## 1. Prerequisites
- Authenticated as an employee with Logistics/Purchasing permissions.
- Signature PIN set up for the user (default for testing: `123456`).
- MasterData (Vendors, Items, Warehouses) and Project data available.

## 2. Purchase Request (PR) Workflow
### 2.1 Create Purchase Request
1. Navigate to **Logistics -> Purchase Requests**.
2. Click **New Purchase Request**.
3. Fill in the **General Information**:
   - Linked Project.
   - Justification/Purpose.
4. Add items in the **Requested Items** tab:
   - Select Item, Qty, and Estimated Price.
5. Click **Create**.
6. **Assertion**: The PR is created in `Draft` status with a unique PR number.

### 2.2 Submit for Approval
1. Open a `Draft` Purchase Request.
2. Click the **Submit for Approval** button in the header action group.
3. Confirm the submission.
4. **Assertion**: Status changes to `Submitted`. Approvers are notified.

### 2.3 Approval & Signature
1. Authenticate as an eligible approver.
2. Open the `Submitted` PR.
3. Click **Approve & Sign**.
4. Enter your **Signature PIN**.
5. **Assertion**: Status changes to `Approved` (if final approver) or remains `Submitted` with recorded signature.

## 3. Purchase Order (PO) Workflow
### 3.1 Create Purchase Order from PR
1. Navigate to **Logistics -> Purchase Orders**.
2. Click **New Purchase Order**.
3. Select an **Approved Purchase Request**.
4. **Assertion**: Items and Project are automatically populated.
5. Select **Vendor** and **Target Warehouse**.
6. Click **Create**.
7. **Assertion**: PO is created in `Draft` status.

### 3.2 Submit & Approve PO
1. Repeat the submission and approval steps similar to the PR workflow.
2. **Assertion**: Status transitions from `Draft` -> `Submitted` -> `Approved`.

### 3.3 Fulfillment Tracking
1. Open an `Approved` PO.
2. Click **Mark as Sent** when the order is sent to the vendor.
   - **Assertion**: Status changes to `Sent`.
3. Click **Mark as Completed** when items are received at the warehouse.
   - **Assertion**: Status changes to `Completed`.

## 4. Master Data
### 4.1 Warehouse Management
1. Navigate to **Logistics -> Warehouses**.
2. Create/Update warehouses with unique codes and operational status.

## 5. Security & Validation
- Ensure only eligible approvers can sign.
- Verify PIN validation prevents unauthorized signatures.
- Check that status transitions are restricted (e.g., cannot complete a Draft PO).
