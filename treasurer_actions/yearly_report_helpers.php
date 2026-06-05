<?php

function getQuarterMonthRange($quarter)
{
  switch ($quarter) {
    case 1:
      return [1, 3];   // Jan to Mar
    case 2:
      return [4, 6];   // Apr to Jun
    case 3:
      return [7, 9];   // Jul to Sep
    case 4:
      return [10, 12]; // Oct to Dec 
    default:
      return [1, 3];  // Default to Q1 if invalid
  }
}

function getWeightMeasureQuarterlyTotals($conn, $year)
{
  $qtrWeightMeasureTotal = [];

  // Loop through all 4 quarters
  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // returns [startMonth, endMonth]

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Fees on weights & measures'
                  AND MONTH(date) BETWEEN ? AND ?
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Store total per quarter, default to 0.00
    $qtrWeightMeasureTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrWeightMeasureTotal; // [Q1 total, Q2 total, Q3 total, Q4 total]
}

function getFranchisingLicensingQuarterlyTotals($conn, $year)
{
  $qtrFranchiseLicenseTotal = [];

  // Loop through all 4 quarters
  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // returns [startMonth, endMonth]

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Franchising & licensing Fees'
                  AND MONTH(date) BETWEEN ? AND ?
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Store total per quarter, default to 0.00
    $qtrFranchiseLicenseTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrFranchiseLicenseTotal; // [Q1 total, Q2 total, Q3 total, Q4 total]
}

function getMayorsPermitQuarterlyTotals($conn, $year)
{
  $qtrMayorsPermitTotal = [];

  // Loop through all 4 quarters
  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // returns [startMonth, endMonth]

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Permit Fees/ Mayor''s Permit Fees'
                  AND MONTH(date) BETWEEN ? AND ?
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrMayorsPermitTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrMayorsPermitTotal; // [Q1 total, Q2 total, Q3 total, Q4 total]
}

function getBurialPermitQuarterlyTotals($conn, $year)
{
  $qtrBurialPermitTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // returns [startMonth, endMonth]

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Burial Permit Fees'
                  AND MONTH(date) BETWEEN ? AND ?
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrBurialPermitTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrBurialPermitTotal; // [Q1 total, Q2 total, Q3 total, Q4 total]
}

function getSanitaryPermitQuarterlyTotals($conn, $year)
{
  $qtrSanitaryPermitTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // [startMonth, endMonth]

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Sanitary Permit Fees'
                  AND MONTH(date) BETWEEN ? AND ?
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrSanitaryPermitTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrSanitaryPermitTotal; // [Q1, Q2, Q3, Q4]
}

function getMarriageLicenseQuarterlyTotals($conn, $year)
{
  $qtrMarriageLicenseTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // [startMonth, endMonth]

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Marriage License'
                  AND MONTH(date) BETWEEN ? AND ?
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrMarriageLicenseTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrMarriageLicenseTotal;
}

function getMarriageApplicationQuarterlyTotals($conn, $year)
{

  $qtrMarriageApplicationTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // [startMonth, endMonth]

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                 WHERE categorization = 'Application for Marriage'
                  AND MONTH(date) BETWEEN ? AND ?
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrMarriageApplicationTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrMarriageApplicationTotal;
}
 
function getCattleAnimalRegistrationQuarterlyTotals($conn, $year)
{

  $qtrCattleAnimalRegistrationTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // [startMonth, endMonth]

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                 WHERE categorization = 'Cattle/Animal Registration fees'
                  AND MONTH(date) BETWEEN ? AND ?
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrCattleAnimalRegistrationTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrCattleAnimalRegistrationTotal;
}

function getPoliceClearanceQuarterlyTotals($conn, $year)
{

  $qtrPoliceClearanceTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // [startMonth, endMonth]

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Police Clearance'
                  AND MONTH(date) BETWEEN ? AND ?
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrPoliceClearanceTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrPoliceClearanceTotal;
}

function getDeathCertQuarterlyTotals($conn, $year)
{

  $qtrDeathCertTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // [startMonth, endMonth]

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Death Certificate'
                  AND MONTH(date) BETWEEN ? AND ?
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrDeathCertTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrDeathCertTotal;
}


function getMarriageCertQuarterlyTotals($conn, $year)
{

  $qtrMarriageCertTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // [startMonth, endMonth]

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                 WHERE categorization = 'Marriage Certificate'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrMarriageCertTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrMarriageCertTotal;
}

function getAssesorCertQuarterlyTotals($conn, $year)
{

  $qtrAssesorCertTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // [startMonth, endMonth]

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Assesor''s Certificate'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrAssesorCertTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrAssesorCertTotal;
}

function getMTOCertQuarterlyTotals($conn, $year)
{

  $qtrMTOCertTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // [startMonth, endMonth]

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'MTO certificate'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrMTOCertTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrMTOCertTotal;
}

function getHealthCertQuarterlyTotals($conn, $year)
{

  $qtrHealthCertTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // [startMonth, endMonth]

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Health Certificate ( Medical)'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrHealthCertTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrHealthCertTotal;
}

function getSecretaryFeeQuarterlyTotals($conn, $year)
{

  $qtrSecretaryFeeTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // [startMonth, endMonth]

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Secretary''s Fees'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrSecretaryFeeTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrSecretaryFeeTotal;
}

function getOtherMayorClearanceQuarterlyTotals($conn, $year)
{
  $qtrOtherMayorClearanceTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);
    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Other Clearance & Cert./Mayor''s Clearance'
                  AND MONTH(date) BETWEEN ? AND ?  
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrOtherMayorClearanceTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrOtherMayorClearanceTotal;
}

function getMedicalDentalLabQuarterlyTotals($conn, $year)
{

  $qtrMedicalDentalLabTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // [startMonth, endMonth]

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                 WHERE categorization = 'Medical, Dental & Laboratory Fees'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrMedicalDentalLabTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrMedicalDentalLabTotal;
}

function getSolemnizationFeeQuarterlyTotals($conn, $year)
{

  $qtrSolemnizationFeeTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // [startMonth, endMonth]

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Solemnization Fees'
                  AND MONTH(date) BETWEEN ? AND ?  
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrSolemnizationFeeTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrSolemnizationFeeTotal;
}

function getAnnotationFeeQuarterlyTotals($conn, $year)
{
  $qtrAnnotationFeeTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // [startMonth, endMonth]
    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                 WHERE categorization = 'Annotation Fees'
                  AND MONTH(date) BETWEEN ? AND ?  
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrAnnotationFeeTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrAnnotationFeeTotal;
}

function getMiscFeeQuarterlyTotals($conn, $year)
{

  $qtrMiscFeeTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); // [startMonth, endMonth]
    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Miscellaneous Fees'
                  AND MONTH(date) BETWEEN ? AND ?  
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrMiscFeeTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrMiscFeeTotal;
}

function getRAQuarterlyTotals($conn, $year)
{

  $qtrRATotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);
    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'RA 9048, 10172 & 9255'
                  AND MONTH(date) BETWEEN ? AND ?  
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrRATotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrRATotal;
}

function getCemeteriesQuarterlyTotals($conn, $year)
{
  $qtrCemeteriesTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);
    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Cemeteries'
                  AND MONTH(date) BETWEEN ? AND ?  
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrCemeteriesTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrCemeteriesTotal;
}

function getInspectionFeeQuarterlyTotals($conn, $year)
{
  $qtrInspectionTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);
    $sql = "SELECT SUM(amount) AS total
                FROM transaction_items
                WHERE categorization = 'Inspection Fees'
                  AND MONTH(date) BETWEEN ? AND ?
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrInspectionTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrInspectionTotal;
}

function getElectricalFeeQuarterlyTotals($conn, $year)
{
  $qtrElectricalTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);
    $sql = "SELECT SUM(amount) AS total
                FROM transaction_items
                WHERE categorization = 'Electrical Fees'
                  AND MONTH(date) BETWEEN ? AND ?
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrElectricalTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrElectricalTotal;
}

function getBuildingPermitFeeQuarterlyTotals($conn, $year)
{
  $qtrBuildingPermitTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);
    $sql = "SELECT SUM(amount) AS total
                FROM transaction_items
                WHERE categorization = 'Building Permit Fees'
                  AND MONTH(date) BETWEEN ? AND ?
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrBuildingPermitTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrBuildingPermitTotal;
}



function getBirthCertQuarterlyTotals($conn, $year)
{
  $qtrBirthCertTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Birth Certificate'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrBirthCertTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrBirthCertTotal;
}

function getBirthCertificateNewBornQuarterlyTotals($conn, $year)
{
  $qtrBirthCertNewBornTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Birth Certificate (New Born) Fees'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrBirthCertNewBornTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrBirthCertNewBornTotal;
}

function getAmusementTaxQuarterlyTotals($conn, $year)
{
  $qtrAmusementTaxTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Amusement Tax ( Provincial Account)'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrAmusementTaxTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrAmusementTaxTotal;
}

function getBusinessTaxesBanksQuarterlyTotals($conn, $year)
{
  $qtrBusinessTaxesBanksTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Business Taxes Banks and Other Financial Institution'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrBusinessTaxesBanksTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrBusinessTaxesBanksTotal;
}

function getBusinessTaxesContractorsQuarterlyTotals($conn, $year)
{
  $qtrBusinessTaxesContractorsTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Business Taxes Contractors'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrBusinessTaxesContractorsTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrBusinessTaxesContractorsTotal;
}

function getBusinessTaxesRetailersQuarterlyTotals($conn, $year)
{
  $qtrBusinessTaxesRetailersTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Business Taxes Retailers'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrBusinessTaxesRetailersTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrBusinessTaxesRetailersTotal;
}

function getBusinessTaxesInterestQuarterlyTotals($conn, $year)
{
  $qtrBusinessTaxesInterestTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Business Taxes Interest/Surcharge'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrBusinessTaxesInterestTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrBusinessTaxesInterestTotal;
}

function getCommunityTaxCorporationQuarterlyTotals($conn, $year)
{
  $qtrCommunityTaxCorporationTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Community Tax - Corporation'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrCommunityTaxCorporationTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrCommunityTaxCorporationTotal;
}

function getCommunityTaxIndividualQuarterlyTotals($conn, $year)
{
  $qtrCommunityTaxIndividualTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Community Tax - Individual'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrCommunityTaxIndividualTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrCommunityTaxIndividualTotal;
}

function getGarbageFeesQuarterlyTotals($conn, $year)
{
  $qtrGarbageFeesTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Garbage Fees'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrGarbageFeesTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrGarbageFeesTotal;
}

function getInterestIncomeQuarterlyTotals($conn, $year)
{
  $qtrInterestIncomeTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Interest Income'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrInterestIncomeTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrInterestIncomeTotal;
}

function getMarilagEcoparkQuarterlyTotals($conn, $year)
{
  $qtrMarilagEcoparkTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Marilag Ecopark'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrMarilagEcoparkTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrMarilagEcoparkTotal;
}

function getMunicipalLotQuarterlyTotals($conn, $year)
{
  $qtrMunicipalLotTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Municipal Lot'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrMunicipalLotTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrMunicipalLotTotal;
}

function getSlaughterHouseOperationsQuarterlyTotals($conn, $year)
{
  $qtrSlaughterHouseOperationsTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Slaugther House Operations'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrSlaughterHouseOperationsTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrSlaughterHouseOperationsTotal;
}

function getStallGoodwillQuarterlyTotals($conn, $year)
{
  $qtrStallGoodwillTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Stall Goodwill'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrStallGoodwillTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrStallGoodwillTotal;
}

function getStallRentalsQuarterlyTotals($conn, $year)
{
  $qtrStallRentalsTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Stall Rentals'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrStallRentalsTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrStallRentalsTotal;
}

function getSTLQuarterlyTotals($conn, $year)
{
  $qtrSTLTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'STL'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrSTLTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrSTLTotal;
}

function getWaterWorksMonthlyDuesQuarterlyTotals($conn, $year)
{
  $qtrWaterWorksMonthlyDuesTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Water Works Monthly dues'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrWaterWorksMonthlyDuesTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrWaterWorksMonthlyDuesTotal;
}

function getWaterWorksRegistrationQuarterlyTotals($conn, $year)
{
  $qtrWaterWorksRegistrationTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Water Works Registration'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrWaterWorksRegistrationTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrWaterWorksRegistrationTotal;
}

function getInterestSurchargeQuarterlyTotals($conn, $year)
{
  $qtrInterestSurchargeTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Business Taxes Interest / Surcharge'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrInterestSurchargeTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrInterestSurchargeTotal;
}

function getBREQSQuarterlyTotals($conn, $year)
{
  $qtrBREQSTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'BREQS'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrBREQSTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrBREQSTotal;
}

function getBackfillingHaulingQuarterlyTotals($conn, $year)
{
  $qtrBackfillingHaulingTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter);

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Backfilling/Hauling'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrBackfillingHaulingTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrBackfillingHaulingTotal;
}

function getZoningClearanceQuarterlyTotals($conn, $year)
{
  $qtrZoningClearanceTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); 

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Zonal/Location Permit Fees'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrZoningClearanceTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrZoningClearanceTotal;
}

function getOthersFeeQuarterlyTotals($conn, $year)
{
  $qtrOthersFeeTotal = [];

  for ($quarter = 1; $quarter <= 4; $quarter++) {
    $months = getQuarterMonthRange($quarter); 

    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Others'
                  AND MONTH(date) BETWEEN ? AND ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $months[0], $months[1], $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrOthersFeeTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrOthersFeeTotal;
}
