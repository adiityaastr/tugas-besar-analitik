import pandas as pd
import sys
import os

# Get directory paths relative to this script
script_dir = os.path.dirname(os.path.abspath(__file__))
base_dir = os.path.dirname(script_dir)  # E:\tugas_besar_analitik\beauty-dashboard
products_path = os.path.join(base_dir, 'storage', 'app', 'product_code - Sheet1.csv')
transactions_path = os.path.join(base_dir, 'storage', 'app', 'transactions - Sheet1.csv')
output_path = os.path.join(base_dir, 'storage', 'app', 'association_rules.csv')

# Load datasets
try:
    products_df = pd.read_csv(products_path)
    transactions_df = pd.read_csv(transactions_path)
except Exception as e:
    print(f"Error loading CSV files: {e}")
    sys.exit(1)

# Clean whitespaces in column names and product codes
products_df.columns = products_df.columns.str.strip()
transactions_df.columns = transactions_df.columns.str.strip()
products_df['product_code'] = products_df['product_code'].str.strip()
transactions_df['product_code'] = transactions_df['product_code'].str.strip()

# Map product_code to product_name
product_map = dict(zip(products_df['product_code'], products_df['product_name']))

# Group products by customer_id to find purchase patterns per customer
print("Grouping transactions by Customer ID...")
customer_baskets = transactions_df.groupby('customer_id')['product_code'].apply(list).reset_index()

# Filter out customers with only 1 transaction (no association possible)
customer_baskets = customer_baskets[customer_baskets['product_code'].apply(len) > 1]
print(f"Total customers with multiple purchases: {len(customer_baskets)}")

# Thresholds
min_support = 0.005
min_confidence = 0.1

# Check if mlxtend is installed
try:
    from mlxtend.frequent_patterns import apriori, association_rules
    from mlxtend.preprocessing import TransactionEncoder
    print(f"Using mlxtend library with min_support={min_support}...")
    
    # Encode transactions for mlxtend
    te = TransactionEncoder()
    te_ary = te.fit(customer_baskets['product_code']).transform(customer_baskets['product_code'])
    df_encoded = pd.DataFrame(te_ary, columns=te.columns_)
    
    # Run Apriori with max_len=4
    frequent_itemsets = apriori(df_encoded, min_support=min_support, use_colnames=True, max_len=4)
    
    if frequent_itemsets.empty:
        print("No frequent itemsets found.")
    else:
        # Generate association rules
        rules = association_rules(frequent_itemsets, metric="confidence", min_threshold=min_confidence)
        
        if rules.empty:
            print("No association rules found.")
        else:
            # Sort by lift and confidence
            rules = rules.sort_values(by=['lift', 'confidence'], ascending=[False, False])
            
            # Map product codes to names
            def map_items(itemset):
                return ", ".join([product_map.get(item, item) for item in itemset])
                
            rules['antecedents_name'] = rules['antecedents'].apply(map_items)
            rules['consequents_name'] = rules['consequents'].apply(map_items)
            
            rules_display = rules[['antecedents_name', 'consequents_name', 'support', 'confidence', 'lift']]
            print(f"Found {len(rules_display)} association rules.")
            
            # Save results to CSV
            rules_display.to_csv(output_path, index=False)
            print(f"Rules saved to {output_path}")

except ImportError:
    print(f"mlxtend library is not installed. Running native Python Apriori with min_support={min_support}...")
    import itertools
    
    # Native Apriori implementation
    total_customers = len(customer_baskets)
    baskets = customer_baskets['product_code'].values
    
    # Count single items
    item_counts = {}
    for basket in baskets:
        for item in set(basket):
            item_counts[item] = item_counts.get(item, 0) + 1
            
    # Filter frequent 1-itemsets (Support >= min_support)
    min_sup_count = total_customers * min_support
    frequent_1 = {item: count / total_customers for item, count in item_counts.items() if count >= min_sup_count}
    print(f"Frequent 1-itemsets count: {len(frequent_1)}")
    
    frequent_itemsets = {}
    frequent_itemsets[1] = frequent_1
    
    # Generate up to 4-itemsets
    for k in range(2, 5):
        k_counts = {}
        for basket in baskets:
            # Only consider items that are frequent
            freq_items = sorted([item for item in set(basket) if item in frequent_1])
            if len(freq_items) >= k:
                for combo in itertools.combinations(freq_items, k):
                    k_counts[combo] = k_counts.get(combo, 0) + 1
                    
        frequent_k = {combo: count / total_customers for combo, count in k_counts.items() if count >= min_sup_count}
        if not frequent_k:
            break
        frequent_itemsets[k] = frequent_k
        print(f"Frequent {k}-itemsets count: {len(frequent_k)}")
        
    # Generate association rules
    native_rules = []
    for k in range(2, 5):
        if k not in frequent_itemsets:
            continue
        for itemset, support in frequent_itemsets[k].items():
            # generate all non-empty proper subsets of itemset
            subsets = []
            for i in range(1, k):
                subsets.extend(itertools.combinations(itemset, i))
                
            for subset in subsets:
                antecedent = tuple(subset)
                consequent = tuple(set(itemset) - set(subset))
                
                # find support of antecedent
                if len(antecedent) == 1:
                    support_a = frequent_itemsets[1][antecedent[0]]
                else:
                    support_a = frequent_itemsets[len(antecedent)].get(antecedent, 0)
                    
                if support_a == 0:
                    continue
                    
                confidence = support / support_a
                if confidence >= min_confidence:
                    # find support of consequent
                    if len(consequent) == 1:
                        support_c = frequent_itemsets[1][consequent[0]]
                    else:
                        support_c = frequent_itemsets[len(consequent)].get(consequent, 0)
                        
                    lift = confidence / support_c if support_c > 0 else 0
                    
                    # map names
                    ant_name = ", ".join([product_map.get(i, i) for i in antecedent])
                    con_name = ", ".join([product_map.get(i, i) for i in consequent])
                    
                    native_rules.append({
                        'antecedents_name': ant_name,
                        'consequents_name': con_name,
                        'support': support,
                        'confidence': confidence,
                        'lift': lift
                    })
        
    if not native_rules:
        print("No association rules found.")
    else:
        rules_df = pd.DataFrame(native_rules)
        rules_df = rules_df.sort_values(by=['lift', 'confidence'], ascending=[False, False])
        print(f"Found {len(rules_df)} association rules (using native fallback).")
        
        # Save to CSV
        rules_df.to_csv(output_path, index=False)
        print(f"Rules saved to {output_path}")
