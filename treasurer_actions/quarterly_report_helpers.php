<?php

function getQuarterMonthRange($quarter)
{
  switch ($quarter) {
    case 'Q1':
      return [1, 3];
    case 'Q2':
      return [4, 6];
    case 'Q3':
      return [7, 9];
    case 'Q4':
      return [10, 12];
    default:
      return [0, 0]; // invalid quarter
  }
}

function getWeightMeasureMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrWeightMeasureTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Fees on weights & measures'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrWeightMeasureTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrWeightMeasureTotal;
}

function getFranchisingLicensingMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrFranchisingLicensingTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Franchising & licensing Fees'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrFranchisingLicensingTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrFranchisingLicensingTotal;
}

function getMayorsPermitMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrMayorsPermitTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Permit Fees/ Mayor''s Permit Fees'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrMayorsPermitTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrMayorsPermitTotal;
}

function getBurialPermitMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrBurialPermitTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Burial Permit Fees'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrBurialPermitTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrBurialPermitTotal;
}

function getSanitaryPermitMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrSanitaryPermitTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Sanitary Permit Fees'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrSanitaryPermitTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrSanitaryPermitTotal;
}

function getMarriageLicenseMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrMarriageLicenseTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Marriage License'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrMarriageLicenseTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrMarriageLicenseTotal;
}

function getMarriageApplicationMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrMarriageApplicationTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Application for Marriage'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrMarriageApplicationTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrMarriageApplicationTotal;
}

function getPoliceClearanceMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrPoliceClearanceTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Police Clearance'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrPoliceClearanceTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrPoliceClearanceTotal;
}

function getDeathCertMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrDeathCertTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Death Certificate'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrDeathCertTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrDeathCertTotal;
}

function getBirthCertMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrBirthCertTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Birth Certificate'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrBirthCertTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrBirthCertTotal;
}

function getMarriageCertMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrMarriageCertTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Marriage Certificate'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrMarriageCertTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrMarriageCertTotal;
}


function getAssesorCertMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrAssesorCertTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
     $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Assessor''s Certificate'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrAssesorCertTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrAssesorCertTotal;
}

function getMTOCertMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrMTOCertTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'MTO certificate'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";          

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrMTOCertTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrMTOCertTotal;
}

function getHealthCertMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrHealthCertTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
     $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Health Certificate ( Medical)'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";           

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrHealthCertTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrHealthCertTotal;
}

function getSecretaryFeeMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrSecretaryFeeTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Secretary''s Fees'
              AND MONTH(date) = ? 
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrSecretaryFeeTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrSecretaryFeeTotal;
}

function getOtherMayorClearanceMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrOtherMayorClearanceTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items 
            WHERE categorization = 'Other Clearance & Cert./Mayor''s Clearance'
              AND MONTH(date) = ? 
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrOtherMayorClearanceTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrOtherMayorClearanceTotal;
}

function getMedicalDentalLabMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrMedicalDentalLabTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Medical, Dental & Laboratory Fees'
              AND MONTH(date) = ? 
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrMedicalDentalLabTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrMedicalDentalLabTotal;
}

function getSolemnizationFeeMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrSolemnizationFeeTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items 
            WHERE categorization = 'Solemnization Fees'
              AND MONTH(date) = ? 
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrSolemnizationFeeTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrSolemnizationFeeTotal;
}

function getAnnotationFeeMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrAnnotationFeeTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Annotation Fees'
              AND MONTH(date) = ? 
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrAnnotationFeeTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrAnnotationFeeTotal;
}

function getMiscFeeMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrMiscFeeTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Miscellaneous Fees'
              AND MONTH(date) = ? 
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrMiscFeeTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrMiscFeeTotal;
}

function getRAMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrRATotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'RA 9048, 10172 & 9255'
              AND MONTH(date) = ? 
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrRATotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrRATotal;
}

function getCemeteriesMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $qtrCemeteriesTotal = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
            FROM transaction_items
            WHERE categorization = 'Cemeteries'
              AND MONTH(date) = ? 
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $qtrCemeteriesTotal[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $qtrCemeteriesTotal;
}
 
function getInspectionFeeMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Inspection Fees'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getElectricalFeeMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Electrical Fees'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getBuildingPermitFeeMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Building Permit Fees'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getZoningClearanceMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Zonal/Location Permit Fees'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

// Additional functions for other categories can be added here following the same pattern
// For example: getAmusementTaxMonthlyTotals, getBusinessTaxesMonthlyTotals, etc.

function getAmusementTaxMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Amusement Tax ( Provincial Account)'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getBackfillingHaulingMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Backfilling/Hauling'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getBREQSMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'BREQS'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getBusinessTaxesBanksMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Business Taxes Banks and Other Financial Institution'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getBusinessTaxesContractorsMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Business Taxes Contractors'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getBusinessTaxesInterestMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter); 
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Business Taxes Interest/Surcharge'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getBusinessTaxesRetailersMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Business Taxes Retailers'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getCattleAnimalRegistrationMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Cattle/Animal Registration fees'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getCommunityTaxCorporationMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Community Tax - Corporation'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getCommunityTaxIndividualMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Community Tax - Individual'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getGarbageFeesMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Garbage Fees'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getInterestIncomeMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Interest Income'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getMarilagEcoparkMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Marilag Ecopark'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getMunicipalLotMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Municipal Lot'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getSlaughterHouseOperationsMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Slaugther House Operations'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getStallGoodwillMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Stall Goodwill'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getStallRentalsMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Stall Rentals'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getSTLMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'STL'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getWaterWorksMonthlyDuesMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Water Works Monthly dues'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getWaterWorksRegistrationMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) AS total
            FROM transaction_items
            WHERE categorization = 'Water Works Registration'
              AND MONTH(date) = ?
              AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals;
}

function getBirthCertNewBornMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Birth Certificate (New Born) Fees'
                  AND MONTH(date) = ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals; // [month1Total, month2Total, month3Total]
}

function getOthersFeeMonthlyTotals($conn, $quarter, $year)
{
  $months = getQuarterMonthRange($quarter);
  $totals = [];

  for ($month = $months[0]; $month <= $months[1]; $month++) {
    $sql = "SELECT SUM(amount) as total
                FROM transaction_items
                WHERE categorization = 'Others'
                  AND MONTH(date) = ? 
                  AND YEAR(date) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
  }

  return $totals; // [month1Total, month2Total, month3Total]
}
