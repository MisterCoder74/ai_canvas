#!/usr/bin/env python3
import re

# Read the file
with open('/home/engine/project/script.js', 'r') as f:
    content = f.read()

# Fix 1: Update startDrag() function
startDrag_old = r'''    draggedElement = e\.currentTarget;
    draggedElement\.classList\.add\('dragging'\);
    
    const rect = draggedElement\.getBoundingClientRect\(\);
    const canvas = document\.getElementById\('canvas'\);
    const canvasRect = canvas\.getBoundingClientRect\(\);
    
    dragOffset\.x = e\.clientX - rect\.left;
    dragOffset\.y = e\.clientY - rect\.top;'''

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

content = re.sub(startDrag_old, startDrag_new, content)

# Fix 2: Update doDrag() function
doDrag_old = r'''    const canvas = document\.getElementById\('canvas'\);
    const canvasRect = canvas\.getBoundingClientRect\(\);
    const scrollLeft = canvas\.parentElement\.scrollLeft;
    const scrollTop = canvas\.parentElement\.scrollTop;
    
    let newX = e\.clientX - canvasRect\.left - dragOffset\.x \+ scrollLeft;
    let newY = e\.clientY - canvasRect\.top - dragOffset\.y \+ scrollTop;'''

doDrag_new = '''    const canvas = document.getElementById('canvas');
    const canvasRect = canvas.getBoundingClientRect();
    const scrollLeft = canvas.parentElement.scrollLeft;
    const scrollTop = canvas.parentElement.scrollTop;
    const currentZoom = zoomLevel || 1;

    // Convert mouse coordinates to canvas logical coordinates (zoom-aware)
    let newX = (e.clientX - canvasRect.left) / currentZoom - dragOffset.x + scrollLeft;
    let newY = (e.clientY - canvasRect.top) / currentZoom - dragOffset.y + scrollTop;'''

content = re.sub(doDrag_old, doDrag_new, content)

# Fix 3: Update handleFenceClick() function
handleFenceClick_old = r'''    const canvas = document\.getElementById\('canvas'\);
    const rect = canvas\.getBoundingClientRect\(\);
    const scrollLeft = canvas\.parentElement\.scrollLeft;
    const scrollTop = canvas\.parentElement\.scrollTop;
    
    const x = e\.clientX - rect\.left \+ scrollLeft;
    const y = e\.clientY - rect\.top \+ scrollTop;'''

handleFenceClick_new = '''    const canvas = document.getElementById('canvas');
    const rect = canvas.getBoundingClientRect();
    const scrollLeft = canvas.parentElement.scrollLeft;
    const scrollTop = canvas.parentElement.scrollTop;
    const currentZoom = zoomLevel || 1;

    // Zoom-aware coordinates
    const x = (e.clientX - rect.left) / currentZoom + scrollLeft;
    const y = (e.clientY - rect.top) / currentZoom + scrollTop;'''

content = re.sub(handleFenceClick_old, handleFenceClick_new, content)

# Fix 4: Update updateFencePreview() function
updateFencePreview_old = r'''    const canvas = document\.getElementById\('canvas'\);
    const rect = canvas\.getBoundingClientRect\(\);
    const scrollLeft = canvas\.parentElement\.scrollLeft;
    const scrollTop = canvas\.parentElement\.scrollTop;
    
    const x = e\.clientX - rect\.left \+ scrollLeft;
    const y = e\.clientY - rect\.top \+ scrollTop;'''

updateFencePreview_new = '''    const canvas = document.getElementById('canvas');
    const rect = canvas.getBoundingClientRect();
    const scrollLeft = canvas.parentElement.scrollLeft;
    const scrollTop = canvas.parentElement.scrollTop;
    const currentZoom = zoomLevel || 1;

    // Zoom-aware coordinates
    const x = (e.clientX - rect.left) / currentZoom + scrollLeft;
    const y = (e.clientY - rect.top) / currentZoom + scrollTop;'''

content = re.sub(updateFencePreview_old, updateFencePreview_new, content)

# Write the file back
with open('/home/engine/project/script.js', 'w') as f:
    f.write(content)

print("File updated successfully!")
