<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\Division;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Position::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // --------------------
        // Ambil Division ID
        // --------------------
        $branchDivisionId = Division::where('name', 'Branch Office')->first()->id;
        $lendingDivisionId = Division::where('name', 'Lending')->first()->id;
        $fundingDivisionId = Division::where('name', 'Funding')->first()->id;
        $creditDivisionId = Division::where('name', 'Credit Analyst')->first()->id;
        $kmaDivisionId = Division::where('name', 'KMA, SAF, IP')->first()->id;
        $operationDivisionId = Division::where('name', 'Operation')->first()->id;
        $hcgaDivisionId = Division::where('name', 'HC & GA')->first()->id;
        $brandingDivisionId = Division::where('name', 'Brand & Promotion')->first()->id;
        $itDivisionId = Division::where('name', 'IT')->first()->id;
        $internalAuditDivisionId = Division::where('name', 'Internal Audit')->first()->id;
        $direksiDivisionId = Division::where('name', 'Board of Directors')->first()->id;
        $commissionerDivisionId = Division::where('name', 'Board of Commissioners')->first()->id;
        $researchDevelopmentDivisionId = Division::where('name', 'Research & Development')->first()->id;

        // --------------------
        // TOP MANAGEMENT
        // --------------------
        $rups = Position::create([
            'title' => 'RUPS',
            'depth' => 0,
            'division_id' => null
        ]);

        $presidentDirector = Position::create([
            'title' => 'President Director',
            'parent_id' => $rups->id,
            'depth' => 1,
            'division_id' => $direksiDivisionId
        ]);

        $presidentCommissioner = Position::create([
            'title' => 'President Commissioner',
            'parent_id' => $rups->id,
            'depth' => 1,
            'division_id' => $commissionerDivisionId
        ]);

        $commissioner = Position::create([
            'title' => 'Commissioner',
            'parent_id' => $rups->id,
            'depth' => 1,
            'division_id' => $commissionerDivisionId
        ]);

        // --------------------
        // DIRECTORS & MANAGERS
        // --------------------
        $businessDirector = Position::create([
            'title' => 'Business Director',
            'parent_id' => $presidentDirector->id,
            'depth' => 2,
            'division_id' => $direksiDivisionId
        ]);

        $operationManager = Position::create([
            'title' => 'Operation Manager',
            'parent_id' => $presidentDirector->id,
            'depth' => 3,
            'division_id' => $operationDivisionId
        ]);

        $hcgaManager = Position::create([
            'title' => 'HC & GA Manager',
            'parent_id' => $presidentDirector->id,
            'depth' => 3,
            'division_id' => $hcgaDivisionId
        ]);

        $internalAuditManager = Position::create([
            'title' => 'Internal Audit Manager',
            'parent_id' => $presidentDirector->id,
            'indirect_supervisor_id' => $presidentCommissioner->id,
            'depth' => 3,
            'division_id' => $internalAuditDivisionId
        ]);

        $itManager = Position::create([
            'title' => 'IT Manager',
            'parent_id' => $presidentDirector->id,
            'depth' => 3,
            'division_id' => $itDivisionId
        ]);

        $brandingManager = Position::create([
            'title' => 'Brand & Promotion Manager',
            'parent_id' => $businessDirector->id,
            'depth' => 3,
            'division_id' => $brandingDivisionId
        ]);

        $fundingManager = Position::create([
            'title' => 'Funding Manager',
            'parent_id' => $businessDirector->id,
            'depth' => 3,
            'division_id' => $fundingDivisionId
        ]);

        $loanManager1 = Position::create([
            'title' => 'Loan Manager 1',
            'parent_id' => $businessDirector->id,
            'depth' => 3,
            'division_id' => $lendingDivisionId
        ]);

        $loanManager2 = Position::create([
            'title' => 'Loan Manager 2',
            'parent_id' => $businessDirector->id,
            'depth' => 3,
            'division_id' => $lendingDivisionId
        ]);

        $branchManager = Position::create([
            'title' => 'Branch Manager',
            'parent_id' => $businessDirector->id,
            'depth' => 3,
            'division_id' => $branchDivisionId
        ]);

        $creditAnalystScHead = Position::create([
            'title' => 'Credit Analyst Sc. Head',
            'parent_id' => $presidentDirector->id,
            'depth' => 4,
            'division_id' => $creditDivisionId
        ]);

        Position::create([
            'title' => 'Credit Analyst Officer',
            'parent_id' => $creditAnalystScHead->id,
            'depth' => 5,
            'division_id' => $creditDivisionId
        ]);

        // --------------------
        // INTERNAL AUDIT SUB
        // --------------------
        $internalAuditScHead = Position::create([
            'title' => 'Internal Audit Sc. Head',
            'parent_id' => $internalAuditManager->id,
            'depth' => 4,
            'division_id' => $internalAuditDivisionId
        ]);

        Position::create([
            'title' => 'Internal Audit Officer',
            'parent_id' => $internalAuditScHead->id,
            'depth' => 5,
            'division_id' => $internalAuditDivisionId
        ]);

        // --------------------
        // Research & Development
        // --------------------
        Position::create([
            'title' => 'Research & Development Officer',
            'parent_id' => $businessDirector->id,
            'depth' => 5,
            'division_id' => $researchDevelopmentDivisionId
        ]);

        // --------------------
        // IT SUB
        // --------------------
        $itScHead = Position::create([
            'title' => 'IT Sc. Head',
            'parent_id' => $itManager->id,
            'depth' => 4,
            'division_id' => $itDivisionId
        ]);

        Position::create([
            'title' => 'IT Support Officer',
            'parent_id' => $itScHead->id,
            'depth' => 5,
            'division_id' => $itDivisionId
        ]);
        Position::create([
            'title' => 'Data Analyst',
            'parent_id' => $itScHead->id,
            'depth' => 5,
            'division_id' => $itDivisionId
        ]);
        Position::create([
            'title' => 'IT Developer',
            'parent_id' => $itScHead->id,
            'depth' => 5,
            'division_id' => $itDivisionId
        ]);
        Position::create([
            'title' => 'IT DevSecOps',
            'parent_id' => $itScHead->id,
            'depth' => 5,
            'division_id' => $itDivisionId
        ]);

        // --------------------
        // Brand & Promotion SUB
        // --------------------
        $brandingScHead = Position::create([
            'title' => 'Brand & Promotion Section Head',
            'parent_id' => $brandingManager->id,
            'depth' => 4,
            'division_id' => $brandingDivisionId
        ]);

        Position::create([
            'title' => 'Brand & Promotion Officer',
            'parent_id' => $brandingScHead->id,
            'depth' => 5,
            'division_id' => $brandingDivisionId
        ]);

        // --------------------
        // KMA, SAF, IP
        // --------------------
        $peKmaApIp = Position::create([
            'title' => 'PE KMA, SAF, IP',
            'parent_id' => $presidentDirector->id,
            'depth' => 3,
            'division_id' => $kmaDivisionId
        ]);

        Position::create([
            'title' => 'KMA, SAF, IP Officer',
            'parent_id' => $peKmaApIp->id,
            'depth' => 5,
            'division_id' => $kmaDivisionId
        ]);

        // --------------------
        // OPERATION SUB
        // --------------------
        $opScHeadHo = Position::create([
            'title' => 'Operation Section Head (Ops)',
            'parent_id' => $operationManager->id,
            'depth' => 4,
            'division_id' => $operationDivisionId
        ]);

        Position::create(['title' => 'Accounting Officer (Ops)', 'parent_id' => $opScHeadHo->id, 'depth' => 5, 'division_id' => $operationDivisionId]);
        Position::create(['title' => 'Loan Admin Officer (Ops)', 'parent_id' => $opScHeadHo->id, 'depth' => 5, 'division_id' => $operationDivisionId]);
        Position::create(['title' => 'Teller (Ops)', 'parent_id' => $opScHeadHo->id, 'depth' => 5, 'division_id' => $operationDivisionId]);
        Position::create(['title' => 'Customer Service (Ops)', 'parent_id' => $opScHeadHo->id, 'depth' => 5, 'division_id' => $operationDivisionId]);
        Position::create(['title' => 'Frontliner MKK', 'parent_id' => $opScHeadHo->id, 'depth' => 5, 'division_id' => $operationDivisionId]);

        $legalAppraiserScHead = Position::create([
            'title' => 'Legal & Appraiser Sc. Head',
            'parent_id' => $operationManager->id,
            'depth' => 4,
            'division_id' => $operationDivisionId
        ]);

        Position::create([
            'title' => 'Legal & Appraiser Officer',
            'parent_id' => $legalAppraiserScHead->id,
            'depth' => 5,
            'division_id' => $operationDivisionId
        ]);

        $cashOfficeHead = Position::create([
            'title' => 'Cash Office Head',
            'parent_id' => $operationManager->id,
            'depth' => 4,
            'division_id' => $operationDivisionId
        ]);

        // --------------------
        // FUNDING SUB
        // --------------------
        $commercialFundingScHead = Position::create([
            'title' => 'Commercial Funding Section Head',
            'parent_id' => $fundingManager->id,
            'depth' => 4,
            'division_id' => $fundingDivisionId
        ]);

        Position::create([
            'title' => 'Relationship Manager Funding (Commercial)',
            'parent_id' => $commercialFundingScHead->id,
            'depth' => 5,
            'division_id' => $fundingDivisionId
        ]);
        
        $retailFundingScHead = Position::create([
            'title' => 'Retail Funding Section Head',
            'parent_id' => $fundingManager->id,
            'depth' => 4,
            'division_id' => $fundingDivisionId
        ]);

        Position::create([
            'title' => 'Relationship Manager Funding (Retail)',
            'parent_id' => $retailFundingScHead->id,
            'depth' => 5,
            'division_id' => $fundingDivisionId
        ]);

        // --------------------
        // HC & GA SUB
        // --------------------
        $hcgaScHead = Position::create([
            'title' => 'HC & GA Sc. Head',
            'parent_id' => $hcgaManager->id,
            'depth' => 4,
            'division_id' => $hcgaDivisionId
        ]);

        Position::create(['title' => 'Talent Acquisition & Development Officer', 'parent_id' => $hcgaScHead->id, 'depth' => 5, 'division_id' => $hcgaDivisionId]);
        Position::create(['title' => 'HC Administration & Compliance Officer', 'parent_id' => $hcgaScHead->id, 'depth' => 5, 'division_id' => $hcgaDivisionId]);

        $genAffOfficer = Position::create([
            'title' => 'General Affair Officer',
            'parent_id' => $hcgaScHead->id,
            'depth' => 5,
            'division_id' => $hcgaDivisionId
        ]);

        Position::create(['title' => 'Office Boy', 'parent_id' => $genAffOfficer->id, 'depth' => 6, 'division_id' => $hcgaDivisionId]);
        Position::create(['title' => 'Security', 'parent_id' => $genAffOfficer->id, 'depth' => 6, 'division_id' => $hcgaDivisionId]);
        Position::create(['title' => 'Driver', 'parent_id' => $genAffOfficer->id, 'depth' => 6, 'division_id' => $hcgaDivisionId]);

        // --------------------
        // BRANCH SUB
        // --------------------
        $opScHeadBranch = Position::create([
            'title' => 'Operation Section Head (Branch)',
            'parent_id' => $branchManager->id,
            'depth' => 4,
            'division_id' => $branchDivisionId
        ]);

        Position::create(['title' => 'Teller (Branch)', 'parent_id' => $opScHeadBranch->id, 'depth' => 5, 'division_id' => $branchDivisionId]);
        Position::create(['title' => 'Customer Service (Branch)', 'parent_id' => $opScHeadBranch->id, 'depth' => 5, 'division_id' => $branchDivisionId]);
        Position::create(['title' => 'Loan Admin Officer (Branch)', 'parent_id' => $opScHeadBranch->id, 'depth' => 5, 'division_id' => $branchDivisionId]);
        Position::create(['title' => 'Accounting Officer (Branch)', 'parent_id' => $opScHeadBranch->id, 'depth' => 5, 'division_id' => $branchDivisionId]);
        Position::create(['title' => 'General Affair Officer (Branch)', 'parent_id' => $opScHeadBranch->id, 'indirect_supervisor_id' => $hcgaManager->id, 'depth' => 5, 'division_id' => $branchDivisionId]);

        Position::create([
            'title' => 'Collection Officer (Branch)',
            'parent_id' => $branchManager->id,
            'depth' => 5,
            'division_id' => $branchDivisionId
        ]);

        $relManLendingScHeadBranch = Position::create([
            'title' => 'Relationship Manager Lending Section Head (Branch)',
            'parent_id' => $branchManager->id,
            'depth' => 4,
            'division_id' => $branchDivisionId
        ]);

        Position::create(['title' => 'Relationship Manager Lending (Branch)', 'parent_id' => $relManLendingScHeadBranch->id, 'depth' => 5, 'division_id' => $branchDivisionId]);
        Position::create(['title' => 'Relationship Manager Funding (Branch)', 'parent_id' => $relManLendingScHeadBranch->id, 'indirect_supervisor_id' => $fundingManager->id, 'depth' => 5, 'division_id' => $branchDivisionId]);

        // --------------------
        // LOAN 1
        // --------------------
        Position::create([
            'title' => 'Credit Review & Monitoring Officer',
            'parent_id' => $loanManager1->id,
            'depth' => 5,
            'division_id' => $lendingDivisionId
        ]);

        $indLoanScHeadLoan1 = Position::create([
            'title' => 'Relationship Manager Lending Section Head (Loan 1)',
            'parent_id' => $loanManager1->id,
            'depth' => 4,
            'division_id' => $lendingDivisionId
        ]);

        Position::create([
            'title' => 'Relationship Manager Lending (Loan 1)',
            'parent_id' => $indLoanScHeadLoan1->id,
            'depth' => 5,
            'division_id' => $lendingDivisionId
        ]);

        $collScHeadLoan1 = Position::create([
            'title' => 'Collection Sc. Head (Loan 1)',
            'parent_id' => $loanManager1->id,
            'depth' => 4,
            'division_id' => $lendingDivisionId
        ]);

        Position::create([
            'title' => 'Collection Officer (Loan 1)',
            'parent_id' => $collScHeadLoan1->id,
            'depth' => 5,
            'division_id' => $lendingDivisionId
        ]);

        // --------------------
        // LOAN 2
        // --------------------
        $indLoanScHeadLoan2 = Position::create([
            'title' => 'Relationship Manager Lending Section Head (Loan 2)',
            'parent_id' => $loanManager2->id,
            'depth' => 4,
            'division_id' => $lendingDivisionId
        ]);

        Position::create([
            'title' => 'Relationship Manager Lending (Loan 2)',
            'parent_id' => $indLoanScHeadLoan2->id,
            'depth' => 5,
            'division_id' => $lendingDivisionId
        ]);

        $collScHeadLoan2 = Position::create([
            'title' => 'Collection Sc. Head (Loan 2)',
            'parent_id' => $loanManager2->id,
            'depth' => 4,
            'division_id' => $lendingDivisionId
        ]);

        Position::create([
            'title' => 'Collection Officer (Loan 2)',
            'parent_id' => $collScHeadLoan2->id,
            'depth' => 5,
            'division_id' => $lendingDivisionId
        ]);
    }
}
