<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // For a clean slate, disable foreign key checks, truncate, and re-enable.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Position::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // --------------------
        // -- TOP MANAGEMENT --
        // --------------------
        $rups = Position::create(['title' => 'RUPS', 'depth' => 0]);
        
        $commissioner = Position::create(['title' => 'Commissioner', 'parent_id' => $rups->id, 'depth' => 1]);
        
        $presidentDirector = Position::create(['title' => 'President Director', 'parent_id' => $rups->id, 'depth' => 1]);

        // -----------------
        // -- DIRECTORS & MANAGERS --
        // -----------------
        $businessDirector = Position::create(['title' => 'Business Director', 'parent_id' => $presidentDirector->id, 'depth' => 2]);
        $operationManager = Position::create(['title' => 'Operation Manager', 'parent_id' => $presidentDirector->id, 'depth' => 3]);
        $hcgaManager = Position::create(['title' => 'HC & GA Manager', 'parent_id' => $presidentDirector->id, 'depth' => 3]);
        $internalAuditManager = Position::create([
            'title' => 'Internal Audit Manager',
            'parent_id' => $presidentDirector->id,
            'indirect_supervisor_id' => $commissioner->id, // Indirect report (dashed line)
            'depth' => 3
        ]);
        
        // ------------------------------------
        // -- POSITIONS UNDER BUSINESS DIRECTOR --
        // ------------------------------------
        $branchManager = Position::create(['title' => 'Branch Manager', 'parent_id' => $businessDirector->id, 'depth' => 3]);
        $loanManager1 = Position::create(['title' => 'Loan Manager 1', 'parent_id' => $businessDirector->id, 'depth' => 3]);
        $loanManager2 = Position::create(['title' => 'Loan Manager 2', 'parent_id' => $businessDirector->id, 'depth' => 3]);
        $fundingManager = Position::create(['title' => 'Funding Manager', 'parent_id' => $businessDirector->id, 'depth' => 3]);
        $bizDevItManager = Position::create(['title' => 'Business Development & IT Manager', 'parent_id' => $businessDirector->id, 'depth' => 3]);
        $brandingManager = Position::create(['title' => 'Branding & Promotion Manager', 'parent_id' => $businessDirector->id, 'depth' => 3]);

        // ------------------------------------
        // -- POSITIONS UNDER OPERATION MANAGER --
        // ------------------------------------
        $cashOfficeHead = Position::create(['title' => 'Cash Office Head', 'parent_id' => $operationManager->id, 'depth' => 4]);
        // Note: The chart does not show any positions under Cash Office Head

        $opScHeadHo = Position::create(['title' => 'Operation Section Head (HO)', 'parent_id' => $operationManager->id, 'depth' => 4]);
        Position::create(['title' => 'Accounting Officer', 'parent_id' => $opScHeadHo->id, 'depth' => 5]);
        Position::create(['title' => 'Loan Admin Officer (HO)', 'parent_id' => $opScHeadHo->id, 'depth' => 5]);
        Position::create(['title' => 'Teller Service (HO)', 'parent_id' => $opScHeadHo->id, 'depth' => 5]);
        Position::create(['title' => 'Customer Service (HO)', 'parent_id' => $opScHeadHo->id, 'depth' => 5]);

        $legalAppraiserScHead = Position::create(['title' => 'Legal & Appraiser Sc. Head', 'parent_id' => $operationManager->id, 'depth' => 4]);
        Position::create(['title' => 'Legal & Appraiser Officer', 'parent_id' => $legalAppraiserScHead->id, 'depth' => 5]);

        // ------------------------------------
        // -- POSITIONS UNDER HC & GA MANAGER --
        // ------------------------------------
        $hcgaScHead = Position::create(['title' => 'HC & GA Sc. Head', 'parent_id' => $hcgaManager->id, 'depth' => 4]);
        $hcgaOff = Position::create(['title' => 'HC & GA Officer', 'parent_id' => $hcgaScHead->id, 'depth' => 5]);
        Position::create(['title' => 'OB, Driver, Security', 'parent_id' => $hcgaOff->id, 'depth' => 6]);

        // -----------------------------------------
        // -- POSITIONS UNDER INTERNAL AUDIT MANAGER --
        // -----------------------------------------
        $internalAuditScHead = Position::create(['title' => 'Internal Audit Sc. Head', 'parent_id' => $internalAuditManager->id, 'depth' => 4]);
        Position::create(['title' => 'Internal Audit Officer', 'parent_id' => $internalAuditScHead->id, 'depth' => 5]);

        $kmaApIpScHead = Position::create(['title' => 'KMA, SAF, IP Sc. Head', 'parent_id' => $presidentDirector->id, 'depth' => 4]);
        Position::create(['title' => 'KMA, SAF, IP Officer', 'parent_id' => $kmaApIpScHead->id, 'depth' => 5]);

        // --------------------------------
        // -- POSITIONS UNDER BRANCH MANAGER --
        // --------------------------------
        Position::create(['title' => 'Collection Officer (Branch)', 'parent_id' => $branchManager->id, 'depth' => 5]);
        Position::create([
            'title' => 'Funding Officer (Branch)', 
            'parent_id' => $branchManager->id, 
            'indirect_supervisor_id' => $fundingManager->id, // Indirect report (dashed line)
            'depth' => 5
        ]);
        $indLoanScHeadBranch = Position::create(['title' => 'Individual Loan Sc. Head (Branch)', 'parent_id' => $branchManager->id, 'depth' => 4]);
        Position::create(['title' => 'Individual Loan Officer (Branch)', 'parent_id' => $indLoanScHeadBranch->id, 'depth' => 5]);

        $opScHeadBranch = Position::create(['title' => 'Operation Section Head (Branch)', 'parent_id' => $branchManager->id, 'depth' => 4]);
        Position::create([
            'title' => 'GA Officer (Branch)', 
            'parent_id' => $opScHeadBranch->id, 
            'indirect_supervisor_id' => $hcgaManager->id, // Indirect report (dashed line)
            'depth' => 5
        ]);
        Position::create(['title' => 'Teller Service (Branch)', 'parent_id' => $opScHeadBranch->id, 'depth' => 5]);
        Position::create(['title' => 'Customer Service (Branch)', 'parent_id' => $opScHeadBranch->id, 'depth' => 5]);
        Position::create(['title' => 'Loan Admin Officer (Branch)', 'parent_id' => $opScHeadBranch->id, 'depth' => 5]);
        Position::create(['title' => 'Accounting Officer (Branch)', 'parent_id' => $opScHeadBranch->id, 'depth' => 5]);

        // --------------------------------
        // -- POSITIONS UNDER LOAN MANAGER 1 --
        // --------------------------------
        Position::create(['title' => 'Credit Review & Monitoring Officer', 'parent_id' => $loanManager1->id, 'depth' => 5]);

        $indLoanScHeadLoan1 = Position::create(['title' => 'Individual Loan Sc. Head (Loan 1)', 'parent_id' => $loanManager1->id, 'depth' => 4]);
        Position::create(['title' => 'Individual Loan Officer (Loan 1)', 'parent_id' => $indLoanScHeadLoan1->id, 'depth' => 5]);
        
        $collScHeadLoan1 = Position::create(['title' => 'Collection Sc. Head (Loan 1)', 'parent_id' => $loanManager1->id, 'depth' => 4]);
        Position::create(['title' => 'Collection Officer (Loan 1)', 'parent_id' => $collScHeadLoan1->id, 'depth' => 5]);

        // --------------------------------
        // -- POSITIONS UNDER LOAN MANAGER 2 --
        // --------------------------------
        $indLoanScHeadLoan2 = Position::create(['title' => 'Individual Loan Sc. Head (Loan 2)', 'parent_id' => $loanManager2->id, 'depth' => 4]);
        Position::create(['title' => 'Individual Loan Officer (Loan 2)', 'parent_id' => $indLoanScHeadLoan2->id, 'depth' => 5]);
        
        $collScHeadLoan2 = Position::create(['title' => 'Collection Sc. Head (Loan 2)', 'parent_id' => $loanManager2->id, 'depth' => 4]);
        Position::create(['title' => 'Collection Officer (Loan 2)', 'parent_id' => $collScHeadLoan2->id, 'depth' => 5]);
        
        // ---------------------------------
        // -- POSITIONS UNDER FUNDING MANAGER --
        // ---------------------------------
        $commFundingScHead = Position::create(['title' => 'Commercial Funding Sc. Head', 'parent_id' => $fundingManager->id, 'depth' => 4]);
        Position::create(['title' => 'Funding Officer (Commercial)', 'parent_id' => $commFundingScHead->id, 'depth' => 5]);
        
        $fundingScHead = Position::create(['title' => 'Funding Sc. Head', 'parent_id' => $fundingManager->id, 'depth' => 4]);
        Position::create(['title' => 'Funding Officer', 'parent_id' => $fundingScHead->id, 'depth' => 5]);

        // ----------------------------------------------------
        // -- POSITIONS UNDER BUSINESS DEVELOPMENT & IT MANAGER --
        // ----------------------------------------------------
        $bizDevScHead = Position::create(['title' => 'Business Development Sc. Head', 'parent_id' => $bizDevItManager->id, 'depth' => 4]);
        Position::create(['title' => 'Market Intelligence & Research Officer', 'parent_id' => $bizDevScHead->id, 'depth' => 5]);

        $itScHead = Position::create(['title' => 'IT Sc. Head', 'parent_id' => $bizDevItManager->id, 'depth' => 4]);
        Position::create(['title' => 'IT Development & Database Development', 'parent_id' => $itScHead->id, 'depth' => 5]);
        Position::create(['title' => 'IT DevSecOps', 'parent_id' => $itScHead->id, 'depth' => 5]);
        
        // -------------------------------------------------
        // -- POSITIONS UNDER BRANDING & PROMOTION MANAGER --
        // -------------------------------------------------
        $brandingScHead = Position::create(['title' => 'Branding & Promotion Sc. Head', 'parent_id' => $brandingManager->id, 'depth' => 4]);
        Position::create(['title' => 'Branding & Promotion Officer', 'parent_id' => $brandingScHead->id, 'depth' => 5]);

        // -------------------------------------------------
        // -- CREDIT ANALYST --
        // -------------------------------------------------
        $creditAnalystScHead = Position::create(['title' => 'Credit Analyst Sc. Head', 'parent_id' => $presidentDirector->id, 'depth' => 4]);
        Position::create(['title' => 'Credit Analyst Officer', 'parent_id' => $creditAnalystScHead->id, 'depth' => 5]);
    }
}