import json
import os
import glob

print("Loading cleaned dataset files...")

# Load all cleaned dataset JSON files
cleaned_dir = '../1. raw dataset/cleaned_dataset'
json_files = glob.glob(os.path.join(cleaned_dir, '*.json'))

all_services = []
for json_file in json_files:
    if 'citizens_charter' in json_file:
        continue
    with open(json_file, 'r', encoding='utf-8') as f:
        office_data = json.load(f)
        for service in office_data.get('services', []):
            service['office'] = office_data.get('service_office', '')
            all_services.append(service)

print(f"Total Services: {len(all_services)}")

documents = []
for service in all_services:
    requirements_text = ' '.join([req.get('document', '')[:100] for req in service.get('requirements', [])[:5]])
    
    fees_list = []
    for step in service.get('process_flow', []):
        if step.get('agency_actions'):
            for action in step['agency_actions']:
                fee = action.get('fees', '')
                if fee and fee.lower() not in ['none', '', 'n/a']:
                    fees_list.append(fee)
    fees_text = ' '.join(fees_list[:3]) if fees_list else service.get('total_fees', 'No fees')
    
    time_list = []
    for step in service.get('process_flow', []):
        if step.get('agency_actions'):
            for action in step['agency_actions']:
                time = action.get('processing_time', '')
                if time and 'included' not in time.lower():
                    time_list.append(time)
    time_text = ' '.join(time_list[:3]) if time_list else service.get('total_processing_time', 'Not specified')
    
    who_may_avail = service.get('who_may_avail', '')
    if isinstance(who_may_avail, list):
        who_may_avail = '; '.join(who_may_avail)
    
    doc = {
        'service_id': service.get('service_id', ''),
        'service_name': service.get('service_name', ''),
        'office': service.get('office', ''),
        'classification': service.get('classification', ''),
        'transaction_type': service.get('type_of_transaction', []),
        'who_may_avail': who_may_avail,
        'description': service.get('description', ''),
        'requirements': service.get('requirements', []),
        'process_flow': service.get('process_flow', []),
        'requirements_text': requirements_text,
        'fees_text': fees_text,
        'time_text': time_text,
        'text': f"{service.get('service_name', '')}. Office: {service.get('office', '')}. Classification: {service.get('classification', '')}. Who may avail: {who_may_avail}. Description: {service.get('description', '')}. Requirements: {requirements_text}. Fees: {fees_text}. Processing time: {time_text}"
    }
    documents.append(doc)

with open('models/documents.json', 'w', encoding='utf-8') as f:
    json.dump(documents, f, ensure_ascii=False, indent=2)

print(f"Regenerated documents.json with {len(documents)} services")
if documents and documents[0].get('process_flow'):
    print(f"Sample process_flow keys: {list(documents[0]['process_flow'][0].keys())}")
    if documents[0]['process_flow'][0].get('agency_actions'):
        print(f"Sample agency_actions keys: {list(documents[0]['process_flow'][0]['agency_actions'][0].keys())}")

print("\nNOTE: You must rebuild the FAISS index!")
print("Run this in your working environment:")
print("  python rebuild_faiss.py")
print("Or re-run cells 4-6 in train_chatbot_system.ipynb")
