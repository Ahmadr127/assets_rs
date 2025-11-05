# Import Logs Filter Feature

## Overview

Added comprehensive filtering functionality to the import batch detail page (`/imports/{id}`) to help users easily find and analyze specific import logs.

## Features Implemented

### 1. **Status Filter**
Filter logs by their import status:
- **All** - Show all logs (default)
- **Imported** - Successfully imported records
- **Updated** - Successfully updated records
- **Error** - Failed records with errors
- **Duplicate** - Duplicate records detected
- **Valid** - Valid records (pre-import validation)

### 2. **Search Functionality**
Search across multiple fields:
- Row index (e.g., "2", "10")
- Mapped data (kode, nama_fixed_asset, etc.)
- Raw Excel data
- Case-insensitive search

### 3. **Sorting Options**
Sort logs by:
- **Row Index** (default, ascending)
- **Status** (alphabetical)
- **Processed At** (timestamp)

### 4. **Quick Filters**
One-click filter buttons with counts:
- **Errors** - Shows failed rows with error count
- **Duplicates** - Shows duplicate rows with count
- **Imported** - Shows successfully imported rows
- **Updated** - Shows updated rows

### 5. **Active Filter Badges**
Visual indicators showing currently active filters with:
- Filter name and value
- Quick remove button (×) for each filter
- Displayed above the filter form

### 6. **Enhanced UI/UX**
- Filter form in collapsible section
- Result count display ("Showing X of Y logs")
- Reset button to clear all filters
- Improved empty state messages
- Pagination with query string preservation

## Technical Implementation

### Controller Changes
**File:** `app/Http/Controllers/ExcelImportController.php`

```php
public function show(Request $request, ImportBatch $batch)
{
    // Filter by status
    if ($request->filled('status') && $request->status !== 'all') {
        $query->where('status', $request->status);
    }
    
    // Search in mapped data
    if ($request->filled('search')) {
        $query->where(function($q) use ($search) {
            $q->whereRaw('LOWER(mapped_data::text) LIKE ?', ['%' . strtolower($search) . '%'])
              ->orWhereRaw('LOWER(row_data::text) LIKE ?', ['%' . strtolower($search) . '%'])
              ->orWhere('row_index', 'LIKE', '%' . $search . '%');
        });
    }
    
    // Sort
    $query->orderBy($sortBy, $sortOrder);
    
    // Paginate with query string
    $logs = $query->paginate(50)->withQueryString();
}
```

### View Changes
**File:** `resources/views/imports/show.blade.php`

Added sections:
1. **Active Filter Badges** - Shows current filters
2. **Filter Form** - Status, search, and sort inputs
3. **Quick Filter Buttons** - One-click status filters
4. **Enhanced Empty State** - Context-aware messages

## Usage Examples

### Example 1: View Only Errors
```
GET /imports/2?status=error
```
Shows only rows that failed during import with error details.

### Example 2: Search for Specific Asset
```
GET /imports/2?search=FA20251105
```
Finds all logs containing "FA20251105" in any field.

### Example 3: Combined Filters
```
GET /imports/2?status=duplicate&search=gedung&sort_by=row_index
```
Shows duplicate records containing "gedung", sorted by row index.

### Example 4: Sort by Processing Time
```
GET /imports/2?sort_by=processed_at&sort_order=desc
```
Shows all logs sorted by most recently processed.

## UI Components

### Filter Form Layout
```
┌─────────────────────────────────────────────────────────┐
│ Active Filters: [Status: ERROR ×] [Search: "test" ×]   │
├─────────────────────────────────────────────────────────┤
│ Status Filter │ Search Box (2 cols) │ Sort Dropdown    │
│ [Dropdown]    │ [Text Input]         │ [Dropdown]       │
├─────────────────────────────────────────────────────────┤
│ Showing 10 of 100 logs          [Reset] [Filter Button]│
├─────────────────────────────────────────────────────────┤
│ Quick Filters:                                          │
│ [Errors (5)] [Duplicates (3)] [Imported (90)] [...]    │
└─────────────────────────────────────────────────────────┘
```

### Quick Filter Buttons
- **Active state**: Solid color background (e.g., red for errors)
- **Inactive state**: Light background with hover effect
- Shows count for each status type
- Icon for visual identification

## Benefits

1. **Faster Troubleshooting** - Quickly find and fix import errors
2. **Better Analysis** - Understand import patterns and issues
3. **Improved UX** - Intuitive interface with visual feedback
4. **Efficient Navigation** - Quick filters reduce clicks
5. **Flexible Search** - Find records by any field value

## Future Enhancements

Potential improvements:
1. **Export Filtered Results** - Download only filtered logs
2. **Date Range Filter** - Filter by processed_at date
3. **Bulk Actions** - Retry/delete multiple selected logs
4. **Advanced Search** - Field-specific search (e.g., only in kode)
5. **Save Filter Presets** - Save commonly used filter combinations
6. **Real-time Filter** - AJAX-based filtering without page reload
7. **Column Visibility** - Toggle which columns to display
8. **Expanded Row Details** - Click to see full JSON data

## Testing Checklist

- [x] Status filter works for all status types
- [x] Search finds records in mapped_data
- [x] Search finds records in row_data
- [x] Search finds records by row_index
- [x] Sorting works for all sort options
- [x] Quick filters apply correct status
- [x] Active filter badges display correctly
- [x] Remove filter badge (×) works
- [x] Reset button clears all filters
- [x] Pagination preserves query parameters
- [x] Empty state shows appropriate message
- [x] Filter count displays correctly
- [x] Combined filters work together

## Performance Considerations

- **PostgreSQL JSON Search**: Uses `::text` casting for JSON field search
- **Indexing**: Consider adding index on `status` column for faster filtering
- **Pagination**: Limits results to 50 per page to maintain performance
- **Query Optimization**: Uses `whereRaw` for JSON search efficiency

## Browser Compatibility

Tested and working on:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Related Files

- `app/Http/Controllers/ExcelImportController.php`
- `resources/views/imports/show.blade.php`
- `app/Models/ImportLog.php`
- `routes/web.php`
