import pandas as pd
import sys

# Load datasets
try:
    products_df = pd.read_csv(r'E:\tugas_besar_analitik\product_code - Sheet1.csv')
    transactions_df = pd.read_csv(r'E:\tugas_besar_analitik\transactions - Sheet1.csv')
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

# Let's set a lower support threshold (e.g. 1.5% or 0.015)
min_support = 0.015
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
    
    # Run Apriori
    frequent_itemsets = apriori(df_encoded, min_support=min_support, use_colnames=True)
    
    if frequent_itemsets.empty:
        print("No frequent itemsets found with the specified min_support.")
    else:
        # Generate association rules
        rules = association_rules(frequent_itemsets, metric="confidence", min_threshold=min_confidence)
        
        if rules.empty:
            print("No association rules found with the specified min_confidence.")
        else:
            # Sort by lift and confidence
            rules = rules.sort_values(by=['lift', 'confidence'], ascending=[False, False])
            
            # Map product codes to names for readability
            def map_items(itemset):
                return ", ".join([product_map.get(item, item) for item in itemset])
                
            rules['antecedents_name'] = rules['antecedents'].apply(map_items)
            rules['consequents_name'] = rules['consequents'].apply(map_items)
            
            # Select and rename columns for display
            rules_display = rules[['antecedents_name', 'consequents_name', 'support', 'confidence', 'lift']]
            
            print(f"\nFound {len(rules_display)} association rules:")
            print(rules_display.head(20).to_string(index=False))
            
            # Save results to CSV
            rules_display.to_csv(r'E:\tugas_besar_analitik\association_rules.csv', index=False)
            print("\nRules saved to E:\\tugas_besar_analitik\\association_rules.csv")

except ImportError:
    print(f"\nmlxtend library is not installed. Let's implement native Python Apriori with min_support={min_support}...")
    
    # Native Apriori implementation for single itemsets and pairs
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
    
    # Count pairs (2-itemsets)
    pair_counts = {}
    for basket in baskets:
        unique_items = list(set(basket))
        for i in range(len(unique_items)):
            for j in range(i+1, len(unique_items)):
                # Ensure ordered key
                pair = tuple(sorted([unique_items[i], unique_items[j]]))
                if pair[0] in frequent_1 and pair[1] in frequent_1:
                    pair_counts[pair] = pair_counts.get(pair, 0) + 1
                    
    # Filter frequent 2-itemsets
    frequent_2 = {pair: count / total_customers for pair, count in pair_counts.items() if count >= min_sup_count}
    print(f"Frequent 2-itemsets count: {len(frequent_2)}")
    
    # Generate association rules
    native_rules = []
    for pair, support in frequent_2.items():
        item_a, item_b = pair
        support_a = frequent_1[item_a]
        support_b = frequent_1[item_b]
        
        # Rule: A -> B
        confidence_a_b = support / support_a
        if confidence_a_b >= min_confidence:
            lift_a_b = confidence_a_b / support_b
            native_rules.append({
                'antecedents_name': product_map.get(item_a, item_a),
                'consequents_name': product_map.get(item_b, item_b),
                'support': support,
                'confidence': confidence_a_b,
                'lift': lift_a_b
            })
        
        # Rule: B -> A
        confidence_b_a = support / support_b
        if confidence_b_a >= min_confidence:
            lift_b_a = confidence_b_a / support_a
            native_rules.append({
                'antecedents_name': product_map.get(item_b, item_b),
                'consequents_name': product_map.get(item_a, item_a),
                'support': support,
                'confidence': confidence_b_a,
                'lift': lift_b_a
            })
        
    if not native_rules:
        print("No association rules found. Try lowering support or confidence thresholds.")
    else:
        rules_df = pd.DataFrame(native_rules)
        rules_df = rules_df.sort_values(by=['lift', 'confidence'], ascending=[False, False])
        print(f"\nFound {len(rules_df)} association rules (using native fallback):")
        print(rules_df.head(20).to_string(index=False))
        
        # Save to CSV
        rules_df.to_csv(r'E:\tugas_besar_analitik\association_rules.csv', index=False)
        print("\nRules saved to E:\\tugas_besar_analitik\\association_rules.csv")
