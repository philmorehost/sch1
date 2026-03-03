<?php
// include/func/pagination.php

function generatePaginationLinks($currentPage, $totalPages, $baseUrl) {
    $html = '<div class="pagination">';
    $maxVisibleLinks = 4; // Max number of page links to show

    // Previous button
    if ($currentPage > 1) {
        $html .= '<a href="' . $baseUrl . '&p=' . ($currentPage - 1) . '" class="page-link">Previous</a>';
    }

    // Page number links
    $startPage = max(1, $currentPage - floor($maxVisibleLinks / 2));
    $endPage = min($totalPages, $startPage + $maxVisibleLinks - 1);

    if ($startPage > 1) {
        $html .= '<a href="' . $baseUrl . '&p=1" class="page-link">1</a>';
        if ($startPage > 2) {
            $html .= '<span class="page-link-dots">...</span>';
        }
    }

    for ($i = $startPage; $i <= $endPage; $i++) {
        $active = ($i == $currentPage) ? 'active' : '';
        $html .= '<a href="' . $baseUrl . '&p=' . $i . '" class="page-link ' . $active . '">' . $i . '</a>';
    }

    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $html .= '<span class="page-link-dots">...</span>';
        }
        $html .= '<a href="' . $baseUrl . '&p=' . $totalPages . '" class="page-link">' . $totalPages . '</a>';
    }

    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<a href="' . $baseUrl . '&p=' . ($currentPage + 1) . '" class="page-link">Next</a>';
    }

    $html .= '</div>';
    return $html;
}
?>