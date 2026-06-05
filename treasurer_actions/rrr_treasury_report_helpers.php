<?php
function getAllTreasuryMonthlyTotals($conn, $year)
{
    // Categories directly tied to categorization in lgupaymentreferences
    $categories = [
        'amusement_tax'                => ['categorization' => 'Amusement Tax ( Provincial Account)'],
        'annotation_fees'              => ['categorization' => 'Annotation Fees'],
        'application_marriage'         => ['categorization' => 'Application for Marriage'],
        'assessor_cert'                => ['categorization' => "Assessor's Certificate"],
        'backfilling_hauling'          => ['categorization' => 'Backfilling/Hauling'],
        'birth_certificate'            => ['categorization' => 'Birth Certificate'],
        'birth_certificate_newborn'    => ['categorization' => 'Birth Certificate (New Born) Fees'],
        'breqs'                        => ['categorization' => 'BREQS'],
        'building_permit'              => ['categorization' => 'Building Permit Fees'],
        'burial_permit'                => ['categorization' => 'Burial Permit Fees'],
        'business_tax_banks'           => ['categorization' => 'Business Taxes Banks and Other Financial Institution'],
        'business_tax_contractors'     => ['categorization' => 'Business Taxes Contractors'],
        'business_tax_interest'        => ['categorization' => 'Business Taxes Interest/Surcharge'],
        'business_tax_retailers'       => ['categorization' => 'Business Taxes Retailers'],
        'cemeteries'                   => ['categorization' => 'Cemeteries'],
        'community_tax_corp'           => ['categorization' => 'Community Tax - Corporation'],
        'community_tax_individual'     => ['categorization' => 'Community Tax - Individual'],
        'death_certificate'            => ['categorization' => 'Death Certificate'],
        'electrical'                   => ['categorization' => 'Electrical Fees'],
        'weights_measures'             => ['categorization' => 'Fees on weights & measures'],
        'franchising'                  => ['categorization' => 'Franchising & licensing Fees'],
        'garbage'                      => ['categorization' => 'Garbage Fees'],
        'health_cert'                  => ['categorization' => 'Health Certificate ( Medical)'],
        'inspection'                   => ['categorization' => 'Inspection Fees'],
        'interest_income'              => ['categorization' => 'Interest Income'],
        'marilag_ecopark'              => ['categorization' => 'Marilag Ecopark'],
        'marriage_cert'                => ['categorization' => 'Marriage Certificate'],
        'marriage_license'             => ['categorization' => 'Marriage License'],
        'medical_dental_lab'           => ['categorization' => 'Medical, Dental & Laboratory Fees'],
        'misc_fees'                    => ['categorization' => 'Miscellaneous Fees'],
        'mto_cert'                     => ['categorization' => 'MTO certificate'],
        'municipal_lot'                => ['categorization' => 'Municipal Lot'],
        'mayor_clearance'              => ['categorization' => 'Other Clearance & Cert./Mayor\'s Clearance'],
        'police_clearance'             => ['categorization' => 'Police Clearance'],
        'ra_acts'                      => ['categorization' => 'RA 9048, 10172 & 9255'],
        'sanitary_permit'              => ['categorization' => 'Sanitary Permit Fees'],
        'secretary_fee'                => ['categorization' => 'Secretary\'s Fees'],
        'slaughter_house'              => ['categorization' => 'Slaugther House Operations'],
        'solemnization'                => ['categorization' => 'Solemnization Fees'],
        'stall_goodwill'               => ['categorization' => 'Stall Goodwill'],
        'stall_rentals'                => ['categorization' => 'Stall Rentals'],
        'stl'                          => ['categorization' => 'STL'],
        'waterworks_dues'              => ['categorization' => 'Water Works Monthly dues'],
        'waterworks_registration'      => ['categorization' => 'Water Works Registration'],
        'zonal_location'               => ['categorization' => 'Zonal/Location Permit Fees'],
        'fsif'               => ['categorization' => 'FSIF'],
        'prof_tax'               => ['categorization' => 'Professional Tax'],
        'others'               => ['categorization' => 'Others'],
        'cash_excess_payroll'               => ['categorization' => 'Cash Excess (Payroll)'],
        'cash_excess_aics_phil'               => ['categorization' => 'Cash Excess (AICS/PHILHEALTH)'],
        'basic' => ['categorization' => 'RPT Collection'],
    ];

    // Special cases that are by PARTICULAR instead of categorization
    $specials = [
        'cattle_registration_ownership' => "Cattle Registration Fee (Ownership)",
        'cattle_registration_transfer'  => "Cattle Registration Fee (Transfer)",
        'tricycle'                      => "Tricycle Operator's Permit", // fixed apostrophe
        'bicycle'                       => "Bicycle Permit",
    ];

    // Permit Fees vs Mayor's Permit Fees split
    $grouped = [
        'permit_fees' => [
            'Construction/Renovation',
            'Business Operation',
            'Certification fee',
        ],
        'mayors_permit_fees' => [
            "Mayor's Permit",
            'Rolling store/Ambulance permit',
        ],
    ];

    $output = [];

    // Run categorization-based
    foreach ($categories as $key => $filter) {
        $output[$key] = array_fill(1, 12, 0.00);

        $sql = "SELECT MONTH(`date`) AS month_num, SUM(`amount`) AS total
            FROM `transaction_items`
            WHERE `categorization` = ?
              AND YEAR(`date`) = ?
            GROUP BY month_num";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $filter['categorization'], $year);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $output[$key][(int)$row['month_num']] = (float)$row['total'];
        }
        $stmt->close();
    }


    // Run specials (exact particulars)
    foreach ($specials as $key => $particular) {
        $output[$key] = array_fill(1, 12, 0.00);

        $sql = "SELECT MONTH(`t`.`date`) AS month_num, SUM(`t`.`amount`) AS total
                FROM `transaction_items` t
                WHERE t.particular = ?
                  AND YEAR(`t`.`date`) = ?
                GROUP BY month_num";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $particular, $year);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $output[$key][(int)$row['month_num']] = (float)$row['total'];
        }
        $stmt->close();
    }

    // Run grouped (multi-particulars)
    foreach ($grouped as $key => $particulars) {
        $output[$key] = array_fill(1, 12, 0.00);

        if (empty($particulars)) {
            continue; // avoid SQL error when array is empty
        }

        $placeholders = implode(',', array_fill(0, count($particulars), '?'));
        $sql = "SELECT MONTH(`t`.`date`) AS month_num, SUM(`t`.`amount`) AS total
                FROM `transaction_items` t
                WHERE t.particular IN ($placeholders)
                  AND YEAR(`t`.`date`) = ?
                GROUP BY month_num";
        $stmt = $conn->prepare($sql);

        $types = str_repeat("s", count($particulars)) . "i";
        $params = [...$particulars, $year];
        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $output[$key][(int)$row['month_num']] = (float)$row['total'];
        }
        $stmt->close();
    }

    return $output;
}
