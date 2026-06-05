<?php

// Function to get the total amount for weight and measure fees 
function getWeightMeasureTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));

  $sql = "
SELECT SUM(amount) AS total
FROM transaction_items
WHERE categorization = 'Fees on weights & measures'
  AND MONTH(date) = ?
  AND YEAR(date) = ?;
";


  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    return $row['total'] ? $row['total'] : 0;
  }
  return 0;
}

// Function to get the total amount for franchising & licensing fees
function getFranchisingLicensingTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));

  $sql = "
SELECT SUM(amount) AS total
FROM transaction_items
WHERE categorization = 'Franchising & licensing Fees'
  AND MONTH(date) = ?
  AND YEAR(date) = ?;
";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    return $row['total'] ? $row['total'] : 0;
  }
  return 0;
}

function getMayorsPermitTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));

  $sql = "SELECT SUM(amount) AS total
FROM transaction_items
WHERE categorization = 'Permit Fees/ Mayor''s Permit Fees'
  AND MONTH(date) = ?
  AND YEAR(date) = ?;
";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    return $row['total'] ? $row['total'] : 0;
  }
  return 0;
}

function getBurialPermitTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));

$sql = "
SELECT SUM(amount) AS total
FROM transaction_items
WHERE categorization = 'Burial Permit Fees'
  AND MONTH(date) = ?
  AND YEAR(date) = ?;
";


  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    return $row['total'] ? $row['total'] : 0;
  }
  return 0;
}

function getSanitaryPermitTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));

 $sql = "
SELECT SUM(amount) AS total
FROM transaction_items
WHERE categorization = 'Sanitary Permit Fees'
  AND MONTH(date) = ?
  AND YEAR(date) = ?;
";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    return $row['total'] ? $row['total'] : 0;
  }
  return 0;
}

function getMarriageLicenseTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));

 $sql = "
SELECT SUM(amount) AS total
FROM transaction_items
WHERE categorization = 'Marriage License'
  AND MONTH(date) = ?
  AND YEAR(date) = ?;
";


  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    return $row['total'] ? $row['total'] : 0;
  }
  return 0;
}

function getMarriageApplicationTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));

  $sql = "
SELECT SUM(amount) AS total
FROM transaction_items
WHERE categorization = 'Application for Marriage'
  AND MONTH(date) = ?
  AND YEAR(date) = ?;
";


  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    return $row['total'] ? $row['total'] : 0;
  }
  return 0;
}

function getPoliceClearanceTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));

 $sql = "
SELECT SUM(amount) AS total
FROM transaction_items
WHERE categorization = 'Police Clearance'
  AND MONTH(date) = ?
  AND YEAR(date) = ?;
";


  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    return $row['total'] ? $row['total'] : 0;
  }
  return 0;
}

function getDeathCertTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));

 $sql = "
SELECT SUM(amount) AS total
FROM transaction_items
WHERE categorization = 'Death Certificate'
  AND MONTH(date) = ?
  AND YEAR(date) = ?;
";


  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    return $row['total'] ? $row['total'] : 0;
  }
  return 0;
}

function getBirthCertTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Birth Certificate'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getMarriageCertTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Marriage Certificate'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getAssesorCertTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Assessor''s Certificate'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getMTOCertTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'MTO certificate'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getHealthCertTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Health Certificate ( Medical)'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getSecretaryFeeTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Secretary''s Fees'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getOtherMayorClearanceTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Other Clearance & Cert./Mayor''s Clearance'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getMedicalDentalLabTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Medical, Dental & Laboratory Fees'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getSolemnizationFeeTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Solemnization Fees'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}


function getAnnotationFeeTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));

  $sql = "SELECT SUM(t.amount) as total
            FROM transaction_items t
            JOIN lgupaymentreferences l 
              ON t.particular LIKE CONCAT('%', l.particulars, '%')
            WHERE l.categorization = 'Annotation Fees'
              AND MONTH(t.date) = ? 
              AND YEAR(t.date) = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    return $row['total'] ? $row['total'] : 0;
  }
  return 0;
}

function getMiscFeeTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Miscellaneous Fees'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getRATotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'RA 9048, 10172 & 9255'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getCemeteriesTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Cemeteries'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getInspectionFeeTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Inspection Fees'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getElectricalFeeTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Electrical Fees'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getBuildingPermitFeeTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Building Permit Fees'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getZoningClearanceTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Zonal/Location Permit Fees'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getAmusementTaxTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Amusement Tax ( Provincial Account)'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getBusinessTaxRetailersTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Business Taxes Retailers'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getBusinessTaxContractorsTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Business Taxes Contractors'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}

function getBusinessTaxBanksTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Business Taxes Banks and Other Financial Institutions'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) return $row['total'] ?: 0;
  return 0;
}


function getBusinessTaxInterestTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Business Taxes Interest/Surcharge'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}

function getCommunityTaxCorporationTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Community Tax - Corporation'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}

function getCommunityTaxIndividualTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Community Tax - Individual'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}

function getCattleRegistrationTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Cattle/Animal Registration fees'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}

function getGarbageFeesTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Garbage Fees'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}

function getSTLTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'STL'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}

function getBackfillingHaulingTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Backfilling/Hauling'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}

function getBREQSTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'BREQS'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}

function getInterestIncomeTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Interest Income'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}

function getMarilagEcoparkTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Marilag Ecopark'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}

function getMunicipalLotTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Municipal Lot'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}

function getStallGoodwillTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Stall Goodwill'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}

function getStallRentalsTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Stall Rentals'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}

function getWaterWorksRegistrationTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Water Works Registration'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}


// Function to get the total amount for Water Works Monthly Dues
function getWaterWorksMonthlyTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Water Works Monthly dues'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}

// Function to get the total amount for Slaughterhouse Operations
function getSlaughterhouseTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Slaugther House Operations'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}

// Function to get the total amount for Birth Certificate (New Born) Fees
function getBirthCertNewBornTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Birth Certificate (New Born) Fees'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}

// Function to get the total amount for Others category
function getOthersFeeTotal($conn, $month, $year)
{
  $monthNum = date('m', strtotime("$month 1, $year"));
  $sql = "SELECT SUM(amount) AS total
          FROM transaction_items
          WHERE categorization = 'Others'
            AND MONTH(date) = ?
            AND YEAR(date) = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $monthNum, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['total'] ?? 0;
}


// ... (any additional functions you need) ...
