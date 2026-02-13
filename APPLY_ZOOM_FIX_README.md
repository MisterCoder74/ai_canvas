# Zoom Drag Offset Fix - Implementation Instructions

## Problem
When the canvas has zoom level != 100% (set via Ctrl+wheel), dragging cards/notes/magnets/fences causes them to be "captured" at an increasing horizontal/vertical distance from the mouse pointer.

## Root Cause
The canvas has `transform: scale(zoomLevel)` applied via CSS. When calculating drag coordinates:
- `canvas.getBoundingClientRect()` returns coordinates in the **scaled** coordinate system (affected by transform)
- `element.style.left/top` are in the **logical** coordinate system (unscaled, 0-3000px)
- The current code doesn't account for this mismatch, causing the offset to accumulate based on scroll position

## Solution
Convert mouse coordinates to canvas logical coordinates by dividing by zoomLevel in both `startDrag()` and `doDrag()` functions.

## Files to Modify
- script.js (startDrag, doDrag, handleFenceClick, updateFencePreview functions)

## Changes Required

### 1. script.js - Update `startDrag()` function (lines ~714-722)

**Find:**
```javascript
    draggedElement = e.currentTarget;
    draggedElement.classList.add('dragging');
    
    const rect = draggedElement.getBoundingClientRect();
    const canvas = document.getElementById('canvas');
    const canvasRect = canvas.getBoundingClientRect();
    
    dragOffset.x = e.clientX - rect.left;
    dragOffset.y = e.clientY - rect.top;
```

**Replace with:**
```javascript
    draggedElement = e.currentTarget;
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
    dragOffset.y = mouseY - elY;
```

### 2. script.js - Update `doDrag()` function (lines ~760-766)

**Find:**
```javascript
    const canvas = document.getElementById('canvas');
    const canvasRect = canvas.getBoundingClientRect();
    const scrollLeft = canvas.parentElement.scrollLeft;
    const scrollTop = canvas.parentElement.scrollTop;
    
    let newX = e.clientX - canvasRect.left - dragOffset.x + scrollLeft;
    let newY = e.clientY - canvasRect.top - dragOffset.y + scrollTop;
```

**Replace with:**
```javascript
    const canvas = document.getElementById('canvas');
    const canvasRect = canvas.getBoundingClientRect();
    const scrollLeft = canvas.parentElement.scrollLeft;
    const scrollTop = canvas.parentElement.scrollTop;
    const currentZoom = zoomLevel || 1;

    // Convert mouse coordinates to canvas logical coordinates (zoom-aware)
    let newX = (e.clientX - canvasRect.left) / currentZoom - dragOffset.x + scrollLeft;
    let newY = (e.clientY - canvasRect.top) / currentZoom - dragOffset.y + scrollTop;
```

### 3. script.js - Update `handleFenceClick()` function (lines ~2293-2299)

**Find:**
```javascript
    const canvas = document.getElementById('canvas');
    const rect = canvas.getBoundingClientRect();
    const scrollLeft = canvas.parentElement.scrollLeft;
    const scrollTop = canvas.parentElement.scrollTop;
    
    const x = e.clientX - rect.left + scrollLeft;
    const y = e.clientY - rect.top + scrollTop;
```

**Replace with:**
```javascript
    const canvas = document.getElementById('canvas');
    const rect = canvas.getBoundingClientRect();
    const scrollLeft = canvas.parentElement.scrollLeft;
    const scrollTop = canvas.parentElement.scrollTop;
    const currentZoom = zoomLevel || 1;

    // Zoom-aware coordinates
    const x = (e.clientX - rect.left) / currentZoom + scrollLeft;
    const y = (e.clientY - rect.top) / currentZoom + scrollTop;
```

### 4. script.js - Update `updateFencePreview()` function (lines ~2343-2349)

**Find:**
```javascript
    const canvas = document.getElementById('canvas');
    const rect = canvas.getBoundingClientRect();
    const scrollLeft = canvas.parentElement.scrollLeft;
    const scrollTop = canvas.parentElement.scrollTop;
    
    const x = e.clientX - rect.left + scrollLeft;
    const y = e.clientY - rect.top + scrollTop;
```

**Replace with:**
```javascript
    const canvas = document.getElementById('canvas');
    const rect = canvas.getBoundingClientRect();
    const scrollLeft = canvas.parentElement.scrollLeft;
    const scrollTop = canvas.parentElement.scrollTop;
    const currentZoom = zoomLevel || 1;

    // Zoom-aware coordinates
    const x = (e.clientX - rect.left) / currentZoom + scrollLeft;
    const y = (e.clientY - rect.top) / currentZoom + scrollTop;
```

## Automated Fix Script
A Python script has been created at `/home/engine/project/apply_zoom_fix.py` that will apply all four fixes automatically.

To execute:
```bash
cd /home/engine/project
python3 apply_zoom_fix.py
```

## Verification
After applying the fix, test the following:
- [ ] Set zoom to 50% or 150% using Ctrl+wheel
- [ ] Scroll canvas to position ~2000px right
- [ ] Drag any card - it should follow the mouse exactly (no offset)
- [ ] Drag magnets - should follow mouse exactly
- [ ] Drag fences - should follow mouse exactly
- [ ] Drag sticky notes - should follow mouse exactly
- [ ] Test at multiple zoom levels (0.5x, 1x, 1.5x, 2x)
- [ ] Test fence drawing at zoom != 100% - should create fence at correct position
- [ ] Test that connections (SVG lines) still draw correctly after dragging at zoom
- [ ] Undo/redo should still work correctly after zoom-aware drag
