<?php

function getInspectionFeeMonthlyTotals($conn, $year)
{
    $monthlyTotals = [];

    for ($month = 1; $month <= 12; $month++) {
        $sql = "SELECT SUM(amount) AS total, COUNT(*) AS row_count
                FROM transaction_items
            WHERE categorization = 'Inspection Fees'
                AND MONTH(date) = ?
                AND YEAR(date) = ?";


        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $monthlyTotals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
    }

    return $monthlyTotals;
}

function getBuildingPermitFeeMonthlyTotals($conn, $year)
{
    $monthlyTotals = [];

    for ($month = 1; $month <= 12; $month++) {
        $sql = "SELECT SUM(amount) AS total, COUNT(*) AS row_count
                FROM transaction_items
            WHERE categorization = 'Building Permit Fees'
                  AND MONTH(date) = ?
                  AND YEAR(date) = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $monthlyTotals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
    }

    return $monthlyTotals;
}


function getElectricalFeeMonthlyTotals($conn, $year)
{
    $monthlyTotals = [];

    for ($month = 1; $month <= 12; $month++) {
        $sql = "SELECT SUM(amount) AS total, COUNT(*) AS row_count
                FROM transaction_items
            WHERE categorization = 'Electrical Fees'
                AND MONTH(date) = ?
                AND YEAR(date) = ?";


        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $monthlyTotals[] = $row['total'] !== null ? (float)$row['total'] : 0.00;
    }

    return $monthlyTotals;
}
