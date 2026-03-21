#!/bin/bash
# Run Lighthouse audit against the local or production site
# Requires: npm install -g lighthouse

URL=${1:-"https://wellcorefitness.com"}

echo "Running Lighthouse audit on ${URL}..."

lighthouse "$URL" \
    --output=html \
    --output-path=./storage/lighthouse-report.html \
    --chrome-flags="--headless --no-sandbox" \
    --only-categories=performance,accessibility,best-practices,seo \
    2>/dev/null

echo "Report saved to storage/lighthouse-report.html"
echo "Open in browser to review scores"
