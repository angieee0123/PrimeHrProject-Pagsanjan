import json
import sys
import io

# Fix encoding for Windows console
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

print("Testing data structure...")

# Test documents.json structure
print("\n1. Checking documents.json structure...")
try:
    with open('../3. training script/models/documents.json', 'r', encoding='utf-8') as f:
        knowledge_base = json.load(f)
    
    print(f"   ✓ Loaded {len(knowledge_base)} services")
    
    # Find a service with clearance
    clearance_service = None
    for service in knowledge_base:
        if 'clearance' in service['service_name'].lower():
            clearance_service = service
            break
    
    if clearance_service:
        print(f"\n   Service: {clearance_service['service_name']}")
        print(f"   Office: {clearance_service['office']}")
        
        # Check process flow
        if clearance_service.get('process_flow'):
            print(f"\n   Process Flow ({len(clearance_service['process_flow'])} steps):")
            for i, step in enumerate(clearance_service['process_flow'][:3], 1):
                step_num = step.get('step_number', step.get('step'))
                client_action = step.get('client_action', '')
                print(f"\n   Step {step_num}: {client_action[:80]}...")
                
                if step.get('agency_actions'):
                    for action in step['agency_actions']:
                        time = action.get('processing_time', '')
                        fees = action.get('fees', '')
                        if time and 'included' not in time.lower():
                            print(f"      ⏱️  {time}", end='')
                            if fees and fees.lower() not in ['none', '']:
                                print(f" | 💰 {fees}", end='')
                            print()
                            break
        
        # Check requirements
        if clearance_service.get('requirements'):
            print(f"\n   Requirements ({len(clearance_service['requirements'])} items):")
            for req in clearance_service['requirements'][:3]:
                doc = req.get('document', '')
                if doc:
                    print(f"      • {doc[:80]}")
        
        # Check fees and time
        print(f"\n   Total Fees: {clearance_service.get('fees_text', 'N/A')}")
        print(f"   Total Time: {clearance_service.get('time_text', 'N/A')}")
        
        print("\n   ✓ Data structure is correct!")
    else:
        print("   ✗ No clearance service found")
        
except Exception as e:
    print(f"   ✗ Error: {e}")
    import traceback
    traceback.print_exc()

print("\n" + "="*60)
print("DATA STRUCTURE TEST COMPLETE")
print("="*60)
