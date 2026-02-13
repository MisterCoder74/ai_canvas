#!/bin/bash
cd /home/engine/project

# Backup original file
cp script.js script.js.backup

# Fix 1: Update startDrag() function
sed -i '717,722s/.*/    const canvas = document.getElementById('\''canvas'\'');\n    const canvasRect = canvas.getBoundingClientRect();\n    const scrollLeft = canvas.parentElement.scrollLeft;\n    const scrollTop = canvas.parentElement.scrollTop;\n    const currentZoom = zoomLevel || 1;\n\n    \/\/ Mouse position in canvas logical coordinates\n    const mouseX = (e.clientX - canvasRect.left) \/ currentZoom + scrollLeft;\n    const mouseY = (e.clientY - canvasRect.top) \/ currentZoom + scrollTop;\n\n    \/\/ Element position (already in logical coordinates)\n    const elX = parseInt(draggedElement.style.left) || 0;\n    const elY = parseInt(draggedElement.style.top) || 0;\n\n    \/\/ Offset in logical coordinates\n    dragOffset.x = mouseX - elX;\n    dragOffset.y = mouseY - elY;/g' script.js

echo "Fixes applied successfully!"
