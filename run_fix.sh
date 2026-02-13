#!/bin/bash
cd /home/engine/project
python3 << 'PYTHON_EOF'
import re

# Read the file
with open('script.js', 'r') as f:
    content = f.read()

# Fix 1: Update startDrag() function
startDrag_old = '''    draggedElement = e.currentTarget;
    draggedElement.classList.add('dragging');
    
    const rect = draggedElement.getBoundingClientRect();
    const canvas = document.getElementById('canvas');
    const canvasRect = canvas.getBoundingClientRect();
    
    dragOffset.x = e.clientX - rect.left;
    dragOffset.y = e.clientY - rect.top;'''

startDrag_new = '''    draggedElement = e.currentTarget;
    draggedElement.classList.add('dragging');

    const canvas = document.getElementById('canvas');
    const canvasRect = canvas.getBoundingClientRect();
    const scrollLeft = canvas.parentElement.scrollLeft;
    const scrollTop = canvas.parentElement.scrollTop;
    const currentZoom = zoomLevel || 1;

    // Mouse position in canvas logical coordinates
    const mouseX = (e.clientX - canvasRect.left) / currentZoom + scrollLeft;
    const mouseY = (e.clientY - canvasRect.top) / currentZoom + scrollTop;

    // Element position (already in logical coordinates)
    const elX = parseInt(draggedElement.style.left) || 0;
    const elY = parseInt(draggedElement.style.top) || 0;

    // Offset in logical coordinates
    dragOffset.x = mouseX - elX;
    dragOffset.y = mouseY - elY;'''

content = content.replace(startDrag_old, startDrag_new)

# Fix 2: Update doDrag() function
doDrag_old = '''    const canvas = document.getElementById('canvas');
    const canvasRect = canvas.getBoundingClientRect();
    const scrollLeft = canvas.parentElement.scrollLeft;
    const scrollTop = canvas.parentElement.scrollTop;
    
    let newX = e.clientX - canvasRect.left - dragOffset.x + scrollLeft;
    let newY = e.clientY - canvasRect.top - dragOffset.y + scrollTop;'''

doDrag_new = '''    const canvas = document.getElementById('canvas');
    const canvasRect = canvas.getBoundingClientRect();
    const scrollLeft = canvas.parentElement.scrollLeft;
    const scrollTop = canvas.parentElement.scrollTop;
    const currentZoom = zoomLevel || 1;

    // Convert mouse coordinates to canvas logical coordinates (zoom-aware)
    let newX = (e.clientX - canvasRect.left) / currentZoom - dragOffset.x + scrollLeft;
    let newY = (e.clientY - canvasRect.top) / currentZoom - dragOffset.y + scrollTop;'''

content = content.replace(doDrag_old, doDrag_new)

# Fix 3: Update handleFenceClick() function
handleFenceClick_old = '''    const canvas = document.getElementById('canvas');
    const rect = canvas.getBoundingClientRect();
    const scrollLeft = canvas.parentElement.scrollLeft;
    const scrollTop = canvas.parentElement.scrollTop;
    
    const x = e.clientX - rect.left + scrollLeft;
    const y = e.clientY - rect.top + scrollTop;'''

handleFenceClick_new = '''    const canvas = document.getElementById('canvas');
    const rect = canvas.getBoundingClientRect();
    const scrollLeft = canvas.parentElement.scrollLeft;
    const scrollTop = canvas.parentElement.scrollTop;
    const currentZoom = zoomLevel || 1;

    // Zoom-aware coordinates
    const x = (e.clientX - rect.left) / currentZoom + scrollLeft;
    const y = (e.clientY - rect.top) / currentZoom + scrollTop;'''

content = content.replace(handleFenceClick_old, handleFenceClick_new)

# Fix 4: Update updateFencePreview() function
updateFencePreview_old = '''    const canvas = document.getElementById('canvas');
    const rect = canvas.getBoundingClientRect();
    const scrollLeft = canvas.parentElement.scrollLeft;
    const scrollTop = canvas.parentElement.scrollTop;
    
    const x = e.clientX - rect.left + scrollLeft;
    const y = e.clientY - rect.top + scrollTop;'''

updateFencePreview_new = '''    const canvas = document.getElementById('canvas');
    const rect = canvas.getBoundingClientRect();
    const scrollLeft = canvas.parentElement.scrollLeft;
    const scrollTop = canvas.parentElement.scrollTop;
    const currentZoom = zoomLevel || 1;

    // Zoom-aware coordinates
    const x = (e.clientX - rect.left) / currentZoom + scrollLeft;
    const y = (e.clientY - rect.top) / currentZoom + scrollTop;'''

content = content.replace(updateFencePreview_old, updateFencePreview_new)

# Write the file back
with open('script.js', 'w') as f:
    f.write(content)

print("File updated successfully!")
PYTHON_EOF
