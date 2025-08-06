@extends('layouts.admin')

@section('title', 'Struktur Organisasi')
@section('header_icon', 'fluent--organization-24-regular-01')
@section('content_header', 'Organization Structure')

@push('styles')
    <style>
        .organization-structure .node {
            cursor: pointer; /* NEW: Ensures the entire node group is interactive */
        }
        .organization-structure .node rect {
            fill: #FFFDEF;
            stroke: #7E1F0E;
            stroke-width: 2px;
            rx: 12px;
            ry: 12px;
        }
        .organization-structure .node:hover rect {
            fill: #edead4;
        }
        .organization-structure .link {
            fill: none;
            stroke: #000000;
            stroke-width: 1px;
        }
        .organization-structure .indirect-link {
            fill: none;
            stroke: #d9534f;
            stroke-width: 2px;
            stroke-dasharray: 5, 5;
        }
        .organization-structure .node-content {
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            padding: 5px;
            box-sizing: border-box;
            font-size: 14px;
            pointer-events: none; /* NEW: Allows clicks to pass through to the parent node group */
        }
        .organization-structure .node-content strong { font-size: 1em; }
        .organization-structure .node-content .employee-name {
            font-size: 0.85em;
            color: #555;
            margin-top: 4px;
            width: 100%;
            word-wrap: break-word;
        }
        .organization-structure .node-content .supervisor-info {
            font-size: 0.75em;
            color: #777;
            margin-top: 2px;
        }
        .tooltip {
            position: absolute;
            background: rgba(0, 0, 0, 0.700);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            pointer-events: none;
            font-size: 12px;
            max-width: 300px;
            white-space: pre-wrap;
            z-index: 1000;
        }
        .organization-structure .card-body {
            padding: 1rem;
            background-color: #FEFEF9;
            position: relative; /* Needed for positioning zoom controls */
        }
        .organization-structure #chart_div {
            position: relative; /* Container for SVG and zoom buttons */
            width: 100%;
            border: 1px solid #ddd;
            overflow: hidden; /* Important for clean zoom boundaries */
        }
        .organization-structure .card-tools {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1rem;
        }
        .organization-structure svg {
            display: block;
            width: 100%;
            height: 80vh; /* Give SVG a fixed height for panning */
            cursor: grab;
        }
        .organization-structure svg:active {
            cursor: grabbing;
        }
        .organization-structure .btn-add {
            background-color: #9A3B3B;
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .organization-structure .btn-add:hover {
            background-color: #7a2f2f;
            color: white;
        }
        .organization-structure .org-title {
            position: relative;
            width: 100%;
            max-width: 1312px;
            margin: 0 auto 40px auto;
            text-align: center;
            font-family: 'Inter', sans-serif;
            font-style: normal;
            font-weight: 700;
            font-size: 20px;
            line-height: 10px;
            letter-spacing: -0.019em;
            color: #000000;
        }
        
        /* --- Styles for Zoom Controls --- */
        .zoom-controls {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .zoom-btn {
            width: 32px;
            height: 32px;
            background-color: #FFFDEF;
            border: 1px solid #7E1F0E;
            color: #7E1F0E;
            border-radius: 8px;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: background-color 0.2s;
            padding-bottom: 4px; /* Adjust icon position */
        }
        .zoom-btn:hover {
            background-color: #edead4;
        }

        @media (max-width: 1024px) {
            .organization-structure .node-content { font-size: 12px; }
        }
        @media (max-width: 768px) {
            .organization-structure .org-title { font-size: 24px; line-height: 36px; }
            .organization-structure .node-content { font-size: 11px; }
        }
    </style>
@endpush

@section('content')
    <div class="organization-structure">
        <div class="card-body">
            <div class="card-tools">
                @php $role = auth()->user()->role; @endphp
                @if (in_array($role, ['superadmin', 'hc']))
                    <a href="{{ route('organization.structure.create') }}" class="btn-add">
                        <i class="fas fa-plus" style="padding-right: 10px"></i>Add Position
                    </a>
                @endif
            </div>

            <div class="org-title">
                ORGANIZATIONAL STRUCTURE OF BPR PERDANA DAYA NUSANTARA
            </div>

            @if (!empty($chartData) && !empty($chartData['nodes']))
                <div id="chart_div">
                    <svg></svg>
                    <!-- Zoom control buttons -->
                    <div class="zoom-controls">
                        <button id="zoom-in" class="zoom-btn">+</button>
                        <button id="zoom-reset" class="zoom-btn">⟳</button> <!-- NEW: Reset Button -->
                        <button id="zoom-out" class="zoom-btn">-</button>
                    </div>
                </div>
            @else
                <div class="text-center p-4">
                    <p>Belum ada data jabatan untuk ditampilkan. Silakan tambahkan jabatan pertama.</p>
                </div>
            @endif

            {{-- Modal Detail for Organizational Node --}}
            @foreach ($chartData['nodes'] as $node)
                @include('organization.components.detail-modal', [
                    'modalId' => 'position-' . $node['id'],
                    'position' => [
                        'title' => $node['title'],
                        'parent' =>
                            optional(collect($chartData['nodes'])->firstWhere('id', $node['parent_id']))[
                                'title'
                            ] ?? null,
                        'indirect_supervisor' => $node['indirect_supervisor'] ?? null,
                        'employees' => $node['employees'] ?? [],
                    ],
                    'editRoute' => route('organization.structure.edit', $node['id']),
                ])
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script>
        function drawChart() {
            const chartData = {!! json_encode($chartData) !!};
            if (!chartData || !chartData.nodes || chartData.nodes.length === 0) {
                console.error("Chart data is missing or empty.");
                return;
            }

            // --- Responsive Configuration ---
            function getResponsiveConfig() {
                const screenWidth = window.innerWidth;
                if (screenWidth < 768) { // Mobile
                    return { nodeWidth: 160, nodeHeight: 90, verticalSpacing: 130, horizontalSpacing: 30, margin: { top: 40, right: 20, bottom: 40, left: 20 } };
                } else if (screenWidth < 1024) { // Tablet
                    return { nodeWidth: 180, nodeHeight: 90, verticalSpacing: 140, horizontalSpacing: 40, margin: { top: 60, right: 30, bottom: 40, left: 30 } };
                } else { // Desktop
                    return { nodeWidth: 200, nodeHeight: 100, verticalSpacing: 150, horizontalSpacing: 50, margin: { top: 80, right: 40, bottom: 40, left: 40 } };
                }
            }

            const config = getResponsiveConfig();
            const { nodeWidth, nodeHeight, verticalSpacing, horizontalSpacing, margin } = config;

            // --- Data Augmentation with Dummy Nodes ---
            const augmentedNodes = [];
            const originalLinks = [];
            const nodeMap = new Map(chartData.nodes.map(n => [n.id, n]));
            chartData.nodes.forEach(node => {
                const finalNode = { ...node };
                if (node.parent_id) {
                    const parentNode = nodeMap.get(node.parent_id);
                    if (parentNode) {
                        originalLinks.push({ sourceId: parentNode.id, targetId: node.id });
                        const depthDiff = node.depth - parentNode.depth;
                        if (depthDiff > 1) {
                            let lastParentId = parentNode.id;
                            for (let i = 1; i < depthDiff; i++) {
                                const dummyDepth = parentNode.depth + i;
                                const dummyId = `dummy-${parentNode.id}-${node.id}-${i}`;
                                augmentedNodes.push({ id: dummyId, parent_id: lastParentId, depth: dummyDepth, isDummy: true });
                                lastParentId = dummyId;
                            }
                            finalNode.parent_id = lastParentId;
                        }
                    }
                }
                augmentedNodes.push(finalNode);
            });

            // --- D3 Hierarchy Setup and Layout ---
            const root = d3.stratify().id(d => d.id).parentId(d => d.parent_id)(augmentedNodes);
            let nextX = 0;
            root.eachAfter(node => {
                if (node.children && node.children.length > 0) {
                    node.x = d3.mean(node.children, d => d.x);
                } else {
                    node.x = nextX;
                    nextX += nodeWidth + horizontalSpacing;
                }
            });
            root.each(node => {
                if (node.children) {
                    for (let i = 0; i < node.children.length - 1; i++) {
                        const leftNode = node.children[i], rightNode = node.children[i + 1];
                        let rightmost = leftNode.x;
                        leftNode.each(n => { rightmost = Math.max(rightmost, n.x); });
                        let leftmost = rightNode.x;
                        rightNode.each(n => { leftmost = Math.min(leftmost, n.x); });
                        const requiredShift = (rightmost - leftmost) + nodeWidth + horizontalSpacing;
                        if (requiredShift > 0) {
                            function shift(n, amount) {
                                n.x += amount;
                                if (n.children) n.children.forEach(c => shift(c, amount));
                            }
                            shift(rightNode, requiredShift);
                        }
                    }
                }
            });
            root.eachAfter(node => {
                if (node.children && node.children.length > 0) {
                    node.x = d3.mean(node.children, d => d.x);
                }
            });

            // --- Get final positions for rendering ---
            const descendants = root.descendants();
            const linksToDraw = originalLinks.map(link => ({
                source: root.find(n => n.id === link.sourceId),
                target: root.find(n => n.id === link.targetId),
            })).filter(l => l.source && l.target);
            const realNodes = descendants.filter(d => !d.data.isDummy);
            descendants.forEach(n => { n.y = n.data.depth * (nodeHeight + verticalSpacing); });

            // --- Dynamic SVG Sizing ---
            let xMin = Infinity, xMax = -Infinity, yMin = Infinity, yMax = -Infinity;
            realNodes.forEach(d => {
                xMin = Math.min(xMin, d.x); xMax = Math.max(xMax, d.x);
                yMin = Math.min(yMin, d.y); yMax = Math.max(yMax, d.y);
            });
            const chartWidth = (xMax - xMin) + nodeWidth;
            const chartHeight = (yMax - yMin) + nodeHeight;

            // --- SVG and Chart Group Setup ---
            const svg = d3.select("#chart_div svg");
            svg.selectAll("*").remove();
            const g = svg.append("g");

            // --- Zoom and Pan Functionality ---
            const zoom = d3.zoom()
                .scaleExtent([0.1, 3])
                .on("zoom", (event) => {
                    g.attr("transform", event.transform);
                });

            svg.call(zoom);

            const svgNode = svg.node();
            const svgWidth = svgNode.clientWidth;
            const svgHeight = svgNode.clientHeight;

            const initialScale = Math.min(
                svgWidth / (chartWidth + margin.left + margin.right),
                svgHeight / (chartHeight + margin.top + margin.bottom)
            ) * 0.95;
            
            // NEW: Store the initial transform
            const initialTransform = d3.zoomIdentity
                .translate(
                    svgWidth / 2 - (xMin + chartWidth / 2) * initialScale, 
                    margin.top
                )
                .scale(initialScale);
            
            // Apply the initial transform
            svg.call(zoom.transform, initialTransform);

            // Connect zoom buttons
            d3.select("#zoom-in").on("click", () => svg.transition().duration(250).call(zoom.scaleBy, 1.3));
            d3.select("#zoom-out").on("click", () => svg.transition().duration(250).call(zoom.scaleBy, 0.7));
            // NEW: Connect reset button
            d3.select("#zoom-reset").on("click", () => svg.transition().duration(250).call(zoom.transform, initialTransform));


            // --- Draw Links ---
            const linksBySource = d3.group(linksToDraw, d => d.source.id);
            linksBySource.forEach((links, sourceId) => {
                const sourceNode = root.find(n => n.id === sourceId);
                if (!sourceNode || links.length === 0) return;
                const junctionY = sourceNode.y + verticalSpacing / 2;
                const childXCoords = links.map(l => l.target.x);
                const minX = d3.min(childXCoords), maxX = d3.max(childXCoords);
                g.append("path").attr("class", "link").attr("d", `M${sourceNode.x},${sourceNode.y + nodeHeight / 2} V${junctionY}`);
                if (links.length > 1) g.append("path").attr("class", "link").attr("d", `M${minX},${junctionY} H${maxX}`);
                links.forEach(link => g.append("path").attr("class", "link").attr("d", `M${link.target.x},${junctionY} V${link.target.y - nodeHeight / 2}`));
            });
            const laneManager = {
                lanes: {},
                getLane: function(startX, endX, startY) {
                    let y = startY;
                    const x1 = Math.min(startX, endX), x2 = Math.max(startX, endX);
                    while (true) {
                        const occupiedSpans = this.lanes[y];
                        if (!occupiedSpans) { this.lanes[y] = [[x1, x2]]; return y; }
                        if (!occupiedSpans.some(span => (x1 <= span[1] && x2 >= span[0]))) { this.lanes[y].push([x1, x2]); return y; }
                        y -= 20;
                    }
                }
            };
            g.selectAll(".indirect-link").data(chartData.indirectLinks || []).enter().append("path").attr("class", "indirect-link")
                .attr("d", d => {
                    const sourceNode = realNodes.find(n => n.id === d.from);
                    const targetNode = realNodes.find(n => n.id === d.to);
                    if (sourceNode && targetNode) {
                        const sourceIsLeft = sourceNode.x < targetNode.x;
                        const sourceExitX = sourceNode.x + (sourceIsLeft ? 1 : -1) * (nodeWidth / 2);
                        const sourceExitY = sourceNode.y;
                        const targetEnterX = targetNode.x + (sourceIsLeft ? 1 : -1) * (-nodeWidth / 5);
                        const targetEnterY = targetNode.y + nodeHeight / 4;
                        const stubX = sourceExitX + (sourceIsLeft ? 1 : -1) * (horizontalSpacing / 2);
                        const startY = targetNode.y - verticalSpacing / 1.5;
                        const intermediateY = laneManager.getLane(stubX, targetEnterX, startY);
                        return `M${sourceExitX},${sourceExitY} H${stubX} V${intermediateY} H${targetEnterX} V${targetEnterY}`;
                    }
                    return null;
                });

            // --- Draw Nodes ---
            const node = g.selectAll(".node").data(realNodes).enter().append("g")
                .attr("class", "node")
                .attr("transform", d => `translate(${d.x},${d.y})`);
            node.append("rect").attr("width", nodeWidth).attr("height", nodeHeight)
                .attr("x", -nodeWidth / 2).attr("y", -nodeHeight / 2);
            node.append("foreignObject").attr("width", nodeWidth).attr("height", nodeHeight)
                .attr("x", -nodeWidth / 2).attr("y", -nodeHeight / 2)
                .html(d => `
                    <div class="node-content">
                        <div><strong>${d.data.title}</strong></div>
                        <div class="employee-name">
                            ${d.data.employees && d.data.employees.length > 0 ? d.data.employees.join(", ") : "<em>Posisi Kosong</em>"}
                        </div>
                        ${d.data.indirect_supervisor ? `<div class="supervisor-info">(Diawasi: ${d.data.indirect_supervisor})</div>` : ""}
                    </div>
                `);

            // --- Tooltip and Modal Click Setup ---
            const tooltip = d3.select("body").append("div")
                .attr("class", "tooltip").style("opacity", 0);
            
            node.on("click", function(event, d) {
                event.stopPropagation();
                const modalId = `position-${d.id}`;
                showDetailModal(modalId);
            }).on("mouseover", (event, d) => {
                tooltip.transition().duration(200).style("opacity", 1);
                tooltip.html(d.data.tooltip || `<strong>${d.data.title}</strong><br>Klik untuk detail.`)
                    .style("left", (event.pageX + 15) + "px")
                    .style("top", (event.pageY - 15) + "px");
            }).on("mouseout", () => {
                tooltip.transition().duration(500).style("opacity", 0);
            });
        }

        // --- Debounced Resize Handler ---
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (document.getElementById('chart_div')) {
                    drawChart();
                }
            }, 250);
        });

        document.addEventListener("DOMContentLoaded", function() {
            if (document.getElementById('chart_div')) {
                drawChart();
            }
        });
    </script>
@endpush
